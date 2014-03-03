<?php
namespace Keratine\Controller;

use ReflectionProperty;
use ReflectionClass;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\QueryBuilder;

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class CrudController extends Controller
{

	/**
	 * Gets the controller's route prefix.
	 *
	 * @return string
	 **/
	abstract protected function getRoutePrefix();


	/**
	 * Gets the entity class.
	 *
	 * @return string
	 **/
	abstract protected function getEntityClass();


	/**
	 * Gets the list columns configuration
	 *
	 * @return array
	 **/
	abstract protected function getColumns();


    /**
     * Gets the form type
     *
     * @return FormTypeInterface
     **/
    abstract protected function getType();


    /**
     * {@inheritdoc}
     */
	public function connect(Application $app)
	{
		$this->container = $app;

		$controllers = $app['controllers_factory'];

		$controllers->get('/new', array($this, 'newAction'))
			->bind( $this->getRoutePrefix() . '_new' );

		$controllers->post('/create', array($this, 'createAction'))
			->bind( $this->getRoutePrefix() . '_create' );

		$controllers->get('/edit/{id}', array($this, 'editAction'))
			->bind( $this->getRoutePrefix() . '_edit' );

		$controllers->post('/update/{id}', array($this, 'updateAction'))
			->bind( $this->getRoutePrefix() . '_update' );

		$controllers->get('/delete/{id}', array($this, 'deleteAction'))
			->bind( $this->getRoutePrefix() . '_delete' );

        $controllers->match('/ajax', array($this, 'ajaxAction'))
            ->bind( $this->getRoutePrefix() . '_ajax' );

        $controllers->match('/position/{id}/{position}', array($this, 'positionAction'))
            ->bind( $this->getRoutePrefix() . '_position' );

        $controllers->get('/{sort}/{order}/{filter}/{filterValue}', array($this, 'indexAction'))
            ->value('sort', null)
            ->value('order', null)
            ->value('filter', '')
            ->value('filterValue', '')
            ->bind( $this->getRoutePrefix() );

		return $controllers;
	}


	public function indexAction($sort, $order, $filter, $filterValue)
	{
        $queryBuilder = $this->getBySortQueryBuilder($sort, $order);

        $query = $queryBuilder->getQuery();

		$entities = $query->getResult();

        $columns = $this->setSortableColumns($this->getColumns());

        // define container on each widget
		foreach ($columns as $column) {
			if (isset($column['widget'])) {
				$column['widget']->setContainer($this->container);
			}
		}

		return $this->get('twig')->render('admin/list.html.twig', array(
        	'prefix'   => $this->getRoutePrefix(),
        	'columns'  => $columns,
        	'sort'     => $sort,
        	'order'    => $order,
			'entities' => $entities,
            'sortable' => $this->isSortable(),
		));
	}

    /**
     * Gets by sort query builder
     *
     * @param string $sort Sortable column
     * @param string $alias Root query alias
     *
     * @return Doctrine\ORM\QueryBuilder
     **/
    protected function getBySortQueryBuilder($sort, $order = 'ASC', $alias = 'a')
    {
        $repository = $this->get('orm.em')->getRepository($this->getEntityClass());

        $queryBuilder = $repository->createQueryBuilder($alias);
        // $alias = current($queryBuilder->getDQLPart('from'))->getAlias();

        if ($sort) {
            // sort using the getQueryBuilderOrderedBy* magic method if defined
            $sortMethodName = 'getQueryBuilderOrderedBy'.ucfirst(strtolower($sort));
            if (method_exists($repository, $sortMethodName)) {
                $queryBuilder = call_user_method_array($sortMethodName, $repository, array($order, $queryBuilder));
            }
            // else sort by property name if exists
            elseif ($sort && property_exists($this->getEntityClass(), $sort)) {
                $queryBuilder->orderBy($alias.'.'.$sort, $order);
            }
        }
        // if empty sort use Gedmo Sortable annotation if enable
        elseif ($sort = $this->getSortableColumn()) {
            // pre order by group if defined
            if ($sortGroup = $this->getSortableGroup()) {
                $queryBuilder->orderBy($alias.'.'.$sortGroup);
            }
            $queryBuilder->addOrderBy($alias.'.'.$sort);
        }

        return $queryBuilder;
    }


    /**
     * Gets the sortable column if defined on entity class
     *
     * @return string
     **/
    protected function getSortableColumn()
    {
        $reader = new AnnotationReader();
        $reflClass = new ReflectionClass($this->getEntityClass());
        foreach ($reflClass->getProperties() as $property) {
            $reflProperty = new ReflectionProperty($this->getEntityClass(), $property->name);
            $annotation = $reader->getPropertyAnnotation($reflProperty, '\Gedmo\Mapping\Annotation\SortablePosition');
            if ($annotation) {
                return $property->name;
            }
        }
    }


    /**
     * Gets the sortable group if defined on entity class
     *
     * @return string
     **/
    protected function getSortableGroup()
    {
        $reader = new AnnotationReader();
        $reflClass = new ReflectionClass($this->getEntityClass());
        foreach ($reflClass->getProperties() as $property) {
            $reflProperty = new ReflectionProperty($this->getEntityClass(), $property->name);
            $annotation = $reader->getPropertyAnnotation($reflProperty, '\Gedmo\Mapping\Annotation\SortableGroup');
            if ($annotation) {
                return $property->name;
            }
        }
    }


    /**
     * Defines a sortable parameter on each column if it's sortable
     *
     * @param array $columns List columns configuration
     *
     * @return array
     **/
    public function setSortableColumns($columns = array())
    {
        $repository = $this->get('orm.em')->getRepository($this->getEntityClass());

        foreach ($columns as $column => $options) {
            $sortMethodName = 'getQueryBuilderOrderedBy'.ucfirst(strtolower($column));
            if (method_exists($repository, $sortMethodName)) {
                $columns[$column]['sortable'] = true;
                continue;
            }
            if (property_exists($this->getEntityClass(), $column)) {
                // OneToMany and ManyToMany relations are not sortable
                $reader = new \Doctrine\Common\Annotations\AnnotationReader();
                $reflProperty = new \ReflectionProperty($this->getEntityClass(), $column);
                $OneToMany = $reader->getPropertyAnnotation($reflProperty, '\Doctrine\ORM\Mapping\OneToMany');
                $ManyToMany = $reader->getPropertyAnnotation($reflProperty, '\Doctrine\ORM\Mapping\ManyToMany');
                if (!$OneToMany && !$ManyToMany) {
                    $columns[$column]['sortable'] = true;
                    continue;
                }
            }
            $columns[$column]['sortable'] = false;
        }

        return $columns;
    }


    /**
     * Checks if entity class defined the Sortable interface
     *
     * @return boolean
     **/
    protected function isSortable()
    {
        return in_array('Gedmo\Sortable\Sortable', class_implements($this->getEntityClass()));
    }


	public function newAction()
    {
        $entityClass = $this->getEntityClass();
        $entity = new $entityClass;

        $form = $this->createCreateForm($entity);

        return $this->get('twig')->render('admin/new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }


    public function createAction(Request $request)
    {
        $entityClass = $this->getEntityClass();
        $entity = new $entityClass;

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('orm.em')->persist($entity);
            $this->get('orm.em')->flush();

            return $this->redirect($this->generateUrl($this->getRoutePrefix()));
        }

        return $this->get('twig')->render('admin/new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }


    protected function createCreateForm($entity)
    {
        $form = $this->createForm($this->getType(), $entity, array(
            'action' => $this->generateUrl($this->getRoutePrefix() . '_create', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'create', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }


	public function editAction($id)
    {
        $entity = $this->get('orm.em')->find($this->getEntityClass(), $id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createEditForm($entity);

        return $this->get('twig')->render('admin/edit.html.twig',  array(
            'entity' => $entity,
            'form'   => $editForm->createView()
        ));
    }

    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('orm.em')->find($this->getEntityClass(), $id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createEditForm($entity);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->get('orm.em')->flush();

            return $this->redirect($this->generateUrl($this->getRoutePrefix() . '_edit', array('id' => $id)));
        }

        return $this->get('twig')->render('admin/edit.html.twig',  array(
            'entity' => $entity,
            'form'   => $editForm->createView()
        ));
    }


    protected function createEditForm($entity)
    {
        $form = $this->createForm($this->getType(), $entity, array(
            'action' => $this->generateUrl($this->getRoutePrefix() . '_update', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'update', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }


    public function deleteAction($id)
    {
    	$entity = $this->get('orm.em')->find($this->getEntityClass(), $id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $this->get('orm.em')->remove($entity);
        $this->get('orm.em')->flush();

        return $this->redirect($this->generateUrl($this->getRoutePrefix()));
    }

    public function ajaxAction(Request $request)
    {
        $id = $request->get('id');

        $entity = $this->get('orm.em')->find($this->getEntityClass(), $id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($entity, $request->get('column'), $request->get('value'));

        $this->get('orm.em')->flush();

        return new Response($id);
    }


    public function positionAction($id, $position)
    {
        $entity = $this->get('orm.em')->find($this->getEntityClass(), $id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $reader = new AnnotationReader();
        $reflProperty = new ReflectionProperty($this->getEntityClass(), 'position');
        $annotation = $reader->getPropertyAnnotation($reflProperty, '\Gedmo\Mapping\Annotation\SortablePosition');

        if ($annotation) {
            $entity->setPosition($position);

            $this->get('orm.em')->persist($entity);
            $this->get('orm.em')->flush();

            return new Response($id);
        }

        return new Response('Not sortable', 405); // 405 = Method Not Allowed
    }

}