<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba del Metodo Store de POST
     */
    public function test_store()
    {
        /*
         * Verificar los Errores
         */
        //$this->withoutExceptionHandling();

        /*
         * Prueba de Envio mediante POST el Titulo en formato JSON
         */
        $response = $this->json('POST', '/api/posts', [
            'title' => 'Post de Prueba',

        ]);
        /*
         * Validaciones
         * Estructura del Json de Respuesta que posea: id, title, create_at, updated_at
         * Verificar que el Json de respuesta contenga el título enviado
         * Verificar Estatus de respuesta sea igual a 201
         */
        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => 'Post de Prueba'])
            ->assertStatus(201); // Ok creado un recurso
        /*
         * Validaciones
         * Verificar si en Base de datos existe un registro con la información enviada
         */

        $this->assertDatabaseHas('posts', ['title' => 'Post de Prueba']);
    }

    public function test_validate_title()
    {
        $response = $this->json('POST', '/api/posts', [
            'title' => '',
        ]);
        // Estatus HTTP 422 no se pudo completar la solicitud
        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_show()
    {
        $post = Post::factory()->create();

        $response = $this->json('GET', "/api/posts/$post->id");

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => $post->title])
            ->assertStatus(200);
    }
    public function test_404_show()
    {
        $rand = rand();

        $response = $this->json('GET', "/api/posts/$rand");

        $response->assertStatus(404);
    }
}
