<?php
namespace Pages\Model;

use Application\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use \Pages\Entity\Page;
 
class PageModel extends TableGateway implements ServiceLocatorAwareInterface
{ 
	protected $sm;
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}

    public function getServiceLocator()
	{
		return $this->sm;
	}
	
	public function getPagesGuide(){
		$select = new Select();
		$select->from($this->table);
		$select->columns(array('id', 'name'));
		$select->where(array('is_delete'=>0, 'is_active'=>1));
		$resultSet = $this->selectWith($select);
		$pages = array();
		if(count($resultSet)>0){
			foreach($resultSet as $p) $pages[$p->id] = $p->name;
		}
		return $pages;
	}
    
    public function fetchAll($condition=null, $paginated=false, $sort=array('date_last_modification'=>'DESC'), $filter=null)
	{
		$select = new Select();
		$select->columns(array(
			'*'
		));
		$select->from(array('p' => $this->table));
		$select->join(array('r' => 'routes'), 'p.route_id = r.id', array('route_name', 'route_param_id', 'layout'), Select::JOIN_INNER);
		$select->join(array('a' => 'aliases'), 'a.route_id=r.id', array('uri'), Select::JOIN_LEFT);
		$select->join(array('u' => 'users'), 'p.author_id=u.id', array('author_name'=>'name'), Select::JOIN_LEFT);
		
		$where = new Where();
		
		if(is_array($condition))
		{
			foreach($condition as $k=>$v)
			{
				if(is_null($v)) $where->isNull($k);
				else $where->equalTo($k, $v);
			}
		}
		
		if(is_array($filter)) 
		{
			foreach($filter as $k=>$v) 
			{
				if(is_numeric($v)) $where->equalTo($k, $v);
				elseif(!empty($v)) $where->like('p.'.$k, '%'.$v.'%');
			}
		}

		$user = $this->sm->get('User');
		if(!$user->isSuper())
		{
			$select->join(array('perm' => 'permissions'), 'p.route_id=perm.routeId', array(), Select::JOIN_INNER);
			
			$roles = $user->getRoles();
			
			if(is_array($roles) && count($roles)>0)
			{	
				$where->nest()->equalTo('perm.user', $user->id)->or->in('perm.role', array_keys($roles))->unnest();
			}
			else
			{
				$where->equalTo('perm.user', $user->id);
			}
			
			$select->group('p.id');
		}
		
		$select->where($where);
		$select->order($sort);

		if($paginated) 
		{
			$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $this->getAdapter(), $this->getResultSetPrototype());
            $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
			
			return $paginator;
        }
		else
		{
			$resultSet = $this->selectWith($select);
			return $resultSet->toArray();
		}
	}
	
	public function getPageById($pageId, $is_system=null)
	{
		$pageId  = (int) $pageId;
		if(!isset($is_system)) $is_system = array(0,1);
		
		$select = new Select();
		$select->columns(array(
			'*'
		));
		$select->from(array('p' => $this->table));
		$select->join(array('r' => 'routes'), 'p.route_id = r.id', array('route_name', 'route_param_id', 'layout'), Select::JOIN_INNER);
		$select->join(array('a' => 'aliases'), 'a.route_id=r.id', array('uri'), Select::JOIN_LEFT);
		$select->join(array('u' => 'users'), 'p.author_id=u.id', array('author_name'=>'name'), Select::JOIN_LEFT);
		$select->where(array('is_system'=>$is_system));
		$select->where->equalTo('p.is_delete', 0);
		
		$select->where->equalTo('p.id', $pageId);
		
		$resultSet = $this->selectWith($select);
		
		$page = $resultSet->current();
		
		return $page;
	}
	
	public function getPageByRouteId($routeId)
	{
		$routeId = (int)$routeId;
		
		$select = new Select();
		$select->columns(array(
			'*',
		));
		$select->from(array('p' => $this->table));
		$select->join(array('r' => 'routes'), 'p.route_id = r.id', array('route_name', 'route_param_id', 'is_active', 'layout'), Select::JOIN_INNER);
		$select->join(array('a' => 'aliases'), 'a.route_id=r.id', array('uri'), Select::JOIN_LEFT);
		$select->join(array('u' => 'users'), 'p.author_id=u.id', array('author_name'=>'name'), Select::JOIN_LEFT);
		$select->where->equalTo('p.is_delete', 0);
		
		
		$select->where->equalTo('p.route_id', $routeId);
			
		$resultSet = $this->selectWith($select);
		
		$page = $resultSet->current();
		
		return $page;
	}
	
	public function add(Page $page)
	{
		$user = $this->sm->get('User');
		$data = array(
			'name'=>$page->name,
			'title'=>$page->title,
			'header'=>$page->header,
			'content'=>$page->content,
			//'is_active'=>$page->is_active,
			'is_delete'=>0,
			'author_id'=>$user->id,
			'date_creation'=> date('Y-m-d H:i:s'),
			'date_last_modification'=> date('Y-m-d H:i:s'),
			'is_system'=>0,
			'description'=>$page->description,
			'keywords'=>$page->keywords,
		);
		$this->insert($data);
		
		return $this->getLastInsertValue();
	}

	public function savePage(Page $page)
	{
		if(!$page->id) $this->add($page);
		else
		{
			$data = array(
				'name'=>$page->name,
				'title'=>$page->title,
				'header'=>$page->header,
				'content'=>$page->content,
				'date_last_modification'=>date('Y-m-d H:i:s'),
				'description'=>$page->description,
				'keywords'=>$page->keywords
			);
			$this->update($data, array('id'=>$page->id));
		}
	}
    
    public function setRoute($routeId, $pageId)
	{
		$this->update(array('route_id'=>$routeId), array('id'=>$pageId));
	}
	
	public function remove($id)
	{
		$id = (int)$id;
		$this->update(array('is_delete'=>1), array('id'=>$id));
	}
	
	public function restore($id)
	{
		$id = (int)$id;
		$this->update(array('is_delete'=>0), array('id'=>$id));
	}
	
	public function setActive($id)
	{
		$id = (int)$id;
		$this->update(array('is_active'=>1), array('id'=>$id));
	}
    
    public function unsetActive($id)
	{
		$id = (int)$id;
		$this->update(array('is_active'=>0), array('id'=>$id));
	}
}