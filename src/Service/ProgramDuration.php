<?php
namespace App\Service;

use App\Entity\Program;

class ProgramDuration {


    public function Calculate(Program $program): string
    {
        $totalTime = 0;
            foreach($program->getSeasons() as $season){
                foreach($season->getEpisodes() as $episode)
                $totalTime = $totalTime + $episode->getDuration(    ); 
            }
        
        return $totalTime;
    }


}