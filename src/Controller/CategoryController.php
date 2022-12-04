<?php
// src/Controller/CategoryController.php
namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\ProgramRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/category', name: 'category_')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render(
            'category/index.html.twig',
            ['categories' => $categories]
        );
    }

    #[Route('/new', name : 'new')]
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if($form->isValid()) { 
                $categoryRepository->save($category, true);
                $this->addFlash(
                    'success',
                    'the load has been successfully set'
                );
                return $this->redirectToRoute('category_index');

            }
        } else {
            $this->addFlash(
                'danger',
                'the load hasnot been successfully set'
             );
        }

        return $this->renderForm('category/new.html.twig',
        [
         'form' => $form,   
        ]);
    }

    #[Route('/{categoryName}', methods: ['GET'], name: 'show')]
    public function show(string $categoryName, CategoryRepository $categoryRepository, ProgramRepository $programRepository): Response
    {
        $category = $categoryRepository->findOneByName($categoryName);
        if (!$category) {
            throw $this->createNotFoundException(
                'No category with the name : ' . $categoryName . ' found in categories list.'
            );
        }
        $programs = $programRepository->findBy(['category' => $category->getId()], ['id' => 'DESC'], 3);
        return $this->render('category/show.html.twig', [
            'programs' => $programs,
            'category' => $category,
        ]);
    }

}
