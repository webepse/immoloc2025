<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class PaginationService{

    private $entityClass; // l'entité sur laquelle on doit faire la pagination
    private $limit = 10;
    private $currentPage = 1;
    // private $manager;

    // public function __construct(EntityManagerInterface $manager)
    // {
    //     $this->manager = $manager;
    // }
    public function __construct(private EntityManagerInterface $manager)
    {}

    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getEntityClass(){
        return $this->entityClass;
    }

    public function setLimit($limit){
        $this->limit = $limit;

        return $this;
    }

    public function getLimit(){
        return $this->limit;
    }

    public function setPage($page){
        $this->currentPage = $page;
        return $this;
    }

    public function getPage(){
        return $this->currentPage;
    }


    // fonction pour les données
    public function getData()
    {
        // calculer l'offset
        $offset = $this->currentPage * $this->limit - $this->limit;
        // demander au repositoy de trouver les éléments (et récup le repo)
        $repo = $this->manager->getRepository($this->entityClass);

        // recup les data
        $data = $repo->findBy([],[],$this->limit,$offset);

        return $data;
    }

    // fonction pour le nombre de page
    public function getPages()
    {
         // récup le repo
         $repo = $this->manager->getRepository($this->entityClass);
         $total = $repo->count();
         $pages = ceil($total / $this->limit);

         return $pages;
    }

}