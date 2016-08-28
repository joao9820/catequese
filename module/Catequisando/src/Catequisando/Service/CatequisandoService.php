<?php
/**
 * Created by PhpStorm.
 * User: IGOR
 * Date: 14/07/2016
 * Time: 16:45
 */

namespace Catequisando\Service;

use Catequisando\Entity\CatequisandoEntity as Entity;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Stdlib\Hydrator\Reflection;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class CatequisandoService extends  Entity{

    public function getCatequisandoPaginator($filter = NULL, $camposFilter = NULL)
    {

        $sql = new \Zend\Db\Sql\Sql($this->getAdapter());

        $select = $sql->select('catequisando')->columns([

            'id_catequisando',
            'id_telefone_residencial',
            'id_telefone_celular',
            'nm_catequisando',


        ])
        ->join('email','catequisando.id_email = email.id_email',['em_email'])
        ->join('telefone','telefone.id_telefone = catequisando.id_telefone_residencial',['nr_ddd_telefone','nr_telefone'])
        ->join('responsavel_catequisando','responsavel_catequisando.id_catequisando = catequisando.id_catequisando',['id_responsavel'],\Zend\Db\Sql\Select::JOIN_LEFT)
        ->join('responsavel','responsavel.id_responsavel = responsavel_catequisando.id_responsavel',['nm_responsavel'],\Zend\Db\Sql\Select::JOIN_LEFT)
        ->join('turma_catequisando','turma_catequisando.id_catequisando = catequisando.id_catequisando',['id_turma'],\Zend\Db\Sql\Select::JOIN_LEFT)
        ->join('turma','turma.id_turma = turma_catequisando.id_turma',['nm_turma'],\Zend\Db\Sql\Select::JOIN_LEFT);



        $where = [
        ];

        if (!empty($filter)) {

            foreach ($filter as $key => $value) {
                if ($value) {

                    if (isset($camposFilter[$key]['mascara'])) {

                        eval("\$value = " . $camposFilter[$key]['mascara'] . ";");
                    }

                    $where[$camposFilter[$key]['filter']] = '%' . $value . '%';
                }
            }
        }

        $select->where($where)->order(['nm_catequisando ASC']);

        return new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\DbSelect($select, $this->getAdapter()));
    }

    public function fetchPaginator($pagina = 1, $itensPagina = 5, $ordem = 'id_catequisando ASC' , $like = null, $itensPaginacao = 5)
    {

        $sql = new \Zend\Db\Sql\Sql($this->getAdapter());
        $select = $sql->select('catequisando')->order($ordem);

        if (isset($like)) {
            $select
                ->where
                ->like('id_catequisando', "%{$like}%")
                ->or
                ->like('nm_catequisando', "%{$like}%");

        }

        $resultSet = new HydratingResultSet(new Reflection(), new Entity());

        $paginatorAdapter = new DbSelect(
            $select,
            $this->getAdapter(),
            $resultSet
        );

        return (new Paginator($paginatorAdapter))
            ->setCurrentPageNumber((int)$pagina)
            ->setItemCountPerPage((int)$itensPagina)
            ->setPageRange((int)$itensPaginacao);
    }

    public function getCatequisandoToArray($id)
    {

        $sql = new \Zend\Db\Sql\Sql($this->getAdapter());

        $select = $sql->select('catequisando')
            ->where([
                'catequisando.id_catequisando'=> $id
            ]);

        return $sql->prepareStatementForSqlObject($select)->execute()->current();
    }

    public function getFiltrarCatequisandoPorNomeToArray($nm_catequisando) {

        $sql = new \Zend\Db\Sql\Sql($this->getAdapter());

        $select = $sql->select('catequisando')
            ->columns(array('nm_catequisando', 'id_catequisando')) #Colunas a retornar. Basta Omitir que ele traz todas as colunas
            ->where([
                    "catequisando.nm_catequisando LIKE ?" => '%' . $nm_catequisando . '%',
                ]);

        return $sql->prepareStatementForSqlObject($select)->execute();
    }

    public function  getCatequisandoTurma($id){
        $sql = new \Zend\Db\Sql\Sql($this->getAdapter());

        $select = $sql->select('turma_catequisando')->columns(['id_turma'])
            ->where(['turma_catequisando.id_catequisando = ?'=>$id]);

        $id_turma = $sql->prepareStatementForSqlObject($select)->execute()->current();

        $select = $sql->select('turma')->columns(['nm_turma'])
            ->where(['turma.id_turma = ?'=>$id_turma['id_turma']]);

        $x =  $sql->prepareStatementForSqlObject($select)->execute()->current();
        return $x['nm_turma'];
    }

    public function  getCatequisandoResponsavel($id){
        $sql =  new \Zend\Db\Sql\Sql($this->getAdapter());

        $select =$sql->select('responsavel_catequisando')->columns(['id_responsavel'])
        ->where(['responsavel_catequisando.id_catequisando = ?' =>$id ]);

        $id_responsavel = $sql->prepareStatementForSqlObject($select)->execute()->current();
        #$id_responsavel['id_responsavel']
        $select=$sql->select('responsavel')->columns(['nm_responsavel'])->where(['responsavel.id_responsavel = ?'=>$id_responsavel['id_responsavel']]);

        $nm_responsavel = $sql->prepareStatementForSqlObject($select)->execute()->current();
         return $nm_responsavel['nm_responsavel'];
    }


} 