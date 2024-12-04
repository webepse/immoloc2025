<?php

namespace App\Service;

use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginationService{

    /**
     * Le nom de l'entité sur laquelle on veut effectuer une pagination
     *
     * @var string
     */
    private string $entityClass; 


    /**
     * Le nombre d'enregistremnet à récupérer
     *
     * @var integer
     */
    private int $limit = 10;

    /**
     * La page courante
     *
     * @var integer
     */
    private int $currentPage = 1;

  
    /**
     * Le chemin vers le template qui conteint la pagination
     *
     * @var string
     */
    private string $route;

     /**
     * un tableau pour ordonner les résultats
     *
     * @var array|null
     */
    private ?array $order = null;


   /**
    * Constructeur de ma classe PaginationService
    *
    * @param EntityManagerInterface $manager
    * @param Environment $twig
    * @param string $templatePath
    * @param RequestStack $request
    */
    public function __construct(private EntityManagerInterface $manager,private Environment $twig, private string $templatePath, RequestStack $request)
    {
        $this->route = $request->getCurrentRequest()->attributes->get('_route');
    }



    /**
     * Permet de spécifier l'entité sur laquelle on souhaite paginer
     *
     * @param string $entityClass
     * @return self
     */
    public function setEntityClass(string $entityClass): self
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * PErmet de récupérer l'entité sur laquelle on est en train de paginer
     *
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * Permet de spécifier le nombre d'enregistrement que l'on souhaite obtenir
     *
     * @param integer $limit
     * @return self
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Permet de récupéer le nombre d'enregistrement qui seront renvoyés
     *
     * @return integer
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Permet de spécifier la page que l'on souhaite afficher
     *
     * @param integer $page
     * @return self
     */
    public function setPage(int $page): self
    {
        $this->currentPage = $page;
        return $this;
    }

    /**
     * Permet de récup la page qui est actuellement affichée
     *
     * @return integer
     */
    public function getPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Permet de spécifier l'ordre que l'on souhaite afficher pour les résultats
     *
     * @param array $myOrder
     * @return self
     */
    public function setOrder(array $myOrder):self 
    {
        $this->order = $myOrder;
        return $this;
    }


    /**
     * Permet de récupérer le tableau des order
     *
     * @return array
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * Permet de récupérer les données paginées pour une entité spécifique
     * @throws Exception si la propriété $entityClass n'est pas définie
     * @return array
     */
    public function getData(): array
    {
        if(empty($this->entityClass))
        {
            throw new \Exception("Vous n'avez pas spécifié l'entité sur laquelle nous devons paginer: utilisez la méthode setEntityClass() de votre objet PaginationService");
        }

        // calculer l'offset
        $offset = $this->currentPage * $this->limit - $this->limit;
    
        return $this->manager
                    ->getRepository($this->entityClass)
                    ->findBy([],$this->order,$this->limit,$offset);
    }

    /**
     * Permet de récupérer le nombre de page qui existent sur une entité donnée
     * @throws Exception si la propriété $entityClass n'est pas configurée
     * @return integer
     */
    public function getPages(): int
    {
        if(empty($this->entityClass))
        {
            throw new \Exception("Vous n'avez pas spécifié l'entité sur laquelle nous devons paginer: utilisez la méthode setEntityClass() de votre objet PaginationService");
        }
         // récup le total
         $total = $this->manager
                ->getRepository($this->entityClass)
                ->count();
         
         return ceil($total / $this->limit);
    }

    public function display(): void
    {
        $this->twig->display($this->templatePath, [
            'page' => $this->currentPage,
            'pages' => $this->getPages(),
            'route' => $this->route
        ]);
    }

    /**
     * Permet de choisir un template de pagination
     *
     * @param string $templatePath
     * @return self
     */
    public function setTemplatePath(string $templatePath): self
    {
        $this->templatePath = $templatePath;
        return $this;
    }

    /**
     * Permet de récupérer le templatePath actuellement utilisé
     *
     * @return string
     */
    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * Permet de changer la route par défaut pour les liens de la navigation
     *
     * @param string $route
     * @return self
     */
    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Permet de récupérer le nom de la route qui sera utlisée sur les liens de la pagination
     *
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

}