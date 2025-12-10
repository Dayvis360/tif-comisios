<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Lista;
use App\Models\Provincia;
use App\Models\Candidato;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CandidatoControllerTest extends TestCase
{   
    use RefreshDatabase; 

    public function listar_candidatos(){
        $provincia = Provincia::factory()->create();
        

    }


    }