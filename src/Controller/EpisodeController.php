<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Form\EpisodeType;
use Symfony\Component\Mime\Email;
use App\Repository\EpisodeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/episode', name: 'episode_')]
class EpisodeController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(EpisodeRepository $episodeRepository): Response
    {
        return $this->render('episode/index.html.twig', [
            'episodes' => $episodeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EpisodeRepository $episodeRepository,SluggerInterface $slugger, MailerInterface $mailer): Response
    {
        $episode = new Episode();
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if($form->isValid()) { 
                $slug = $slugger->slug($episode->getTitle());
                $episode->setSlug($slug);
                $episodeRepository->save($episode, true);
                $this->addFlash(
                    'success',
                    'the load has been successfully set'
                );

                $email = (new Email())
                ->from('hello@example.com')
                ->to('you@example.com')
                ->subject('Nouvel épisode sur Wild Series!')
                ->html($this->renderView('episode/newEpisodeEmail.html.twig',
                [
                    'episode' => $episode,
                ]
                ));
    
                $mailer->send($email);

                return $this->redirectToRoute('episode_index');

            }
        } else {
            $this->addFlash(
                'danger',
                'the load hasnot been successfully set'
             );
        }

        return $this->renderForm('episode/new.html.twig', [
            'episode' => $episode,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(Episode $episode): Response
    {
        return $this->render('episode/show.html.twig', [
            'episode' => $episode,
        ]);
    }

    #[Route('/{slug}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Episode $episode, EpisodeRepository $episodeRepository): Response
    {
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if($form->isValid()) { 
                $episodeRepository->save($episode, true);
                $this->addFlash(
                    'success',
                    'the load has been successfully set'
                );
                return $this->redirectToRoute('episode_index');

            }
        } else {
            $this->addFlash(
                'danger',
                'the load hasnot been successfully set'
             );
        }

        return $this->renderForm('episode/edit.html.twig', [
            'episode' => $episode,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Episode $episode, EpisodeRepository $episodeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$episode->getId(), $request->request->get('_token'))) {
            $this->addFlash(
               'danger',
               'Well deleted'
            );
            $episodeRepository->remove($episode, true);
        }

        return $this->redirectToRoute('index', [], Response::HTTP_SEE_OTHER);
    }
}
