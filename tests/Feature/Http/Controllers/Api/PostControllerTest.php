<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Post;
use App\Models\User;
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
        $user = User::factory()->create();

        /*
         * Prueba de Envio mediante POST el Titulo en formato JSON
         */
        $response = $this->actingAs($user,'api')->json('POST', '/api/posts', [
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

        $user = User::factory()->create();
        $response = $this->actingAs($user,'api')->json('POST', '/api/posts', [
            'title' => '',
        ]);
        // Estatus HTTP 422 no se pudo completar la solicitud
        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_show()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user,'api')->json('GET', "/api/posts/$post->id");

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => $post->title])
            ->assertStatus(200);
    }

    public function test_404_show()
    {
        $rand = rand();
        $user = User::factory()->create();
        $response = $this->actingAs($user,'api')->json('GET', "/api/posts/$rand");

        $response->assertStatus(404);
    }

    public function test_update()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $response = $this->actingAs($user,'api')->json('PUT', "/api/posts/$post->id", [
            'title' => 'Post Modificado',
        ]);
        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => 'Post Modificado'])
            ->assertStatus(200); // Ok creado un recurso

        $this->assertDatabaseHas('posts', ['title' => 'Post Modificado']);
    }

    public function test_delete()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $response = $this->actingAs($user,'api')->json('DELETE', "/api/posts/$post->id");
        $response->assertSee(null)
            ->assertStatus(204); // Sin contenido

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_index()
    {
        $posts = Post::factory(5)->create();
        $user = User::factory()->create();
        $response = $this->actingAs($user,'api')->json('GET', '/api/posts');
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'created_at', 'updated_at']
            ]
        ])->assertStatus(200);
    }

    public function test_guest()
    {
        $this->json('GET','/api/posts')->assertStatus(401);
        $this->json('POST','/api/posts')->assertStatus(401);
        $this->json('GET','/api/posts/1000')->assertStatus(401);
        $this->json('PUT','/api/posts/1000')->assertStatus(401);
        $this->json('DELETE','/api/posts/1000')->assertStatus(401);

    }
}
