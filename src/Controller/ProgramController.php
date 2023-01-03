<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Season;
use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Form\ProgramType;
use App\Form\SearchProgramType;
use App\Service\ProgramDuration;
use Symfony\Component\Mime\Email;
use App\Repository\SeasonRepository;
use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/program', name: 'program_')]
class ProgramController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, ProgramRepository $programRepository): Response
    {
        $form = $this->createForm(SearchProgramType::class);
        $form->handleRequest($request);
        $lookedUpProgram = '';  
        if($form->isSubmitted()&&$form->isValid()) {
            $lookedUpProgram = $form->getData()['search'];
            $programs = $programRepository->findLikeNameInProgramActors($lookedUpProgram);
        } else {
            $programs = $programRepository->findAll();
        }

        return $this->renderForm(
            'program/index.html.twig',
            [
                'programs' => $programs,
                'form' => $form,                
            ]
        );
    }

    #[Route('/{id}/addWatchlist', name: 'addToWatchList', methods: ["GET","POST"])]
    public function addToWatchlist(Request $request,EntityManagerInterface $entityManager, Program $program, ProgramDuration $programDuration, ProgramRepository $programRepository): Response
    {
        $this->getUser()->addToWatchlist($program);

        $entityManager->persist($this->getUser());
        $entityManager->flush();
        //dd('passe dans addToWatchList');
        
        return $this->json([

            'isInWatchlist' => $this->getUser()->isInWatchlist($program),
            'programId' => $program->getId()
       
            ]);
       
        // return $this->render(
        //     'program/show.html.twig',
        //     [
        //         'program' => $program,
        //         'programDuration' => $programDuration->Calculate($program),
        //     ]
        // );

    }

    #[Route('/{id}/removeWatchlist', name: 'removeFromWatchList', methods: ["GET","POST"])]
    public function removeFromWatchlist(Request $request,EntityManagerInterface $entityManager, Program $program, ProgramDuration $programDuration, ProgramRepository $programRepository): Response
    {
        
        $this->getUser()->removeFromWatchlist($program);

        $entityManager->persist($this->getUser());
        $entityManager->flush();
        //dd('passe dans removeFromWatchList');

         return $this->json([

            'isInWatchlist' => $this->getUser()->isInWatchlist($program),
            'programId' => $program->getId()

     ]);

        // return $this->render(
        //     'program/show.html.twig',
        //     [
        //         'program' => $program,
        //         'programDuration' => $programDuration->Calculate($program),
        //     ]
        // );

    }


    #[Route("/new",name : "new")]
    public function new(Request $request, ProgramRepository $programRepository, SluggerInterface $slugger, MailerInterface $mailer): Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if($form->isValid()) { 
                ///
/*
                $photo = $form->get('poster')->getData();

                // this condition is needed because the 'brochure' field is not required
                // so the PDF file must be processed only when a file is uploaded
                if ($photo) {
                    $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$photo->guessExtension();
    
                    // Move the file to the directory where brochures are stored
                    try {
                        //dd($this->getParameter(name: 'image_directory'));exit();
                        $photo->move(
                            $this->getParameter(name: 'image_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
    
                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $program->setPoster($newFilename);
                ///
                }
*/
                $slug = $slugger->slug($program->getTitle());
                $program->setSlug($slug);
                $program->setOwner($this->getUser());
                //dd($program);exit();
                $programRepository->save($program, true);
                $this->addFlash(
                   'success',
                   'the load has been successfully set'
                );
                $from = $this->getParameter('mailer_from');
                $email = (new Email())
                    ->from($from)
                    ->to('your_email@example.com')
                    ->subject('Une nouvelle série vient d\'être publiée !: ' . $program->getTitle())
                    ->html($this->renderView('program/newProgramEmail.html.twig',
                [
                    'program' => $program,
                ]));

                $mailer->send($email);


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

    #[Route('/{slug}', methods: ['GET'], name: 'show')]
    public function show(Program $program, ProgramDuration $programDuration): Response
    {
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : ' . $id . ' found in program\'s table.'
            );
        }


        return $this->render('program/show.html.twig', [
            'program' => $program,
            'programDuration' => $programDuration->Calculate($program),
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

    #[Route('/{slug}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Program $program, ProgramRepository $programRepository): Response
    {
        if ($this->getUser() !== $program->getOwner()) {
            // If not the owner, throws a 403 Access Denied exception
            throw $this->createAccessDeniedException('Only the owner can edit the program!');
        }
        
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

    #[Route('/{slug}', name: 'delete', methods: ['POST'])]
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
