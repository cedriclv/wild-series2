<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Program;
use App\Form\ProgramType;
use App\Repository\SeasonRepository;
use App\Repository\ProgramRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/program', name: 'program_')]
class ProgramController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProgramRepository $programRepository): Response
    {
        $programs = $programRepository->findAll();
        return $this->render(
            'program/index.html.twig',
            ['programs' => $programs]
        );
    }

    #[Route("/new",name : "new")]
    public function new(Request $request, ProgramRepository $programRepository): Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if($form->isValid()) { 
                $programRepository->save($program, true);
                $this->addFlash(
                   'success',
                   'the load has been successfully set'
                );
                return $this->redirectToRoute('program_index');
            }

        } else {
            $this->addFlash(
                'danger',
                'the load hasnot been successfully set'
             );
        }

        return $this->renderForm('program/new.html.twig',[
            'form' => $form,
        ]);

    }

    #[Route('/{id}', methods: ['GET'],  requirements: ['id' => '\d+'], name: 'show')]
    public function show(Program $program): Response
    {
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : ' . $id . ' found in program\'s table.'
            );
        }
        return $this->render('program/show.html.twig', [
            'program' => $program,
        ]);
    }

    #[Route('/{program}/season/{season}', methods: ['GET'],  requirements: ['id' => '\d+'], name: 'season_show')]
    public function showSeason(Season $season, Program $program): Response
    {
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : ' . $id . ' found in program\'s table.'
            );
        }
        if (!$season) {
            throw $this->createNotFoundException(
                'No season with id : ' . $id . ' found in season\'s table.'
            );
        }
       //dd($season);
        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'season' => $season,
        ]);
    }

    #[Route('/{program}/season/{season}/episode/{episode}', methods: ['GET'],  requirements: ['id' => '\d+'], name: 'episode_show')]
    public function showEpisode(Program $program, Season $season, Episode $episode): Response
    {
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : ' . $id . ' found in program\'s table.'
            );
        }
        if (!$season) {
            throw $this->createNotFoundException(
                'No season with id : ' . $id . ' found in season\'s table.'
            );
        }
        if (!$episode) {
            throw $this->createNotFoundException(
                'No episode with id : ' . $id . ' found in episode\'s table.'
            );
        }

        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,            
        ]);
    }

    #[Route('/{id}/edit', name: 'app_program_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Program $program, ProgramRepository $programRepository): Response
    {
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if($form->isValid()) { 
                $programRepository->save($program, true);
                $this->addFlash(
                    'success',
                    'the load has been successfully set'
                 );
                return $this->redirectToRoute('program_index');
            }
        } else {
            $this->addFlash(
                'danger',
                'the load hasnot been successfully set'
             );
        }

        return $this->renderForm('program/edit.html.twig', [
            'program' => $program,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_program_delete', methods: ['POST'])]
    public function delete(Request $request, Program $program, ProgramRepository $programRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$program->getId(), $request->request->get('_token'))) {
            $programRepository->remove($program, true);
            $this->addFlash(
                'danger',
                'Well deleted'
             ); 
        }

        return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
    }

}
