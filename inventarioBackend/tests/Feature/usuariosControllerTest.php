<?
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\usuariosModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UsuariosControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_listar_usuarios()
    {
        usuariosModel::factory()->count(3)->create();

        $response = $this->getJson(route('usuarios.listar'));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'correo', 'nombre', 'tipo', 'created_at', 'updated_at']
                ]
            ]);
    }

   
    /** @test */
public function puede_registrar_un_usuario(){
    $usuarioData = [
        'correo' => 'correo@ejemplo.com',
        'nombre' => 'Nuevo Usuario',
        'contraseña' => 'password123',
        'tipo' => 'admin',
    ];

    $response = $this->postJson(route('usuarios.registrarUsuario'), $usuarioData);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 'success',
            'message' => 'Usuario creado exitosamente',
        ])
        ->assertJsonPath('data.correo', 'correo@ejemplo.com');

    $this->assertDatabaseHas('usuarios', ['correo' => 'correo@ejemplo.com']);
}
 
/** @test */
public function puede_buscar_un_usuario(){
    $usuario = usuariosModel::factory()->create(['nombre' => 'Usuario de Prueba']);

    $response = $this->getJson(route('usuarios.buscarUsuario', ['nombre' => 'Prueba']));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['nombre' => 'Usuario de Prueba']);

}
 /** @test */
 public function no_encuentra_usuarios(){
    $response = $this->getJson(route('usuarios.listar'));

    $response->assertStatus(404)
        ->assertJson([
            'status' => 'error',
            'message' => 'no se encontraron usuarios',
            'statusCode' => 404,
        ]);
    }       
    

/** @test */
public function puede_actualizar_un_usuario(){
    $usuario = usuariosModel::factory()->create();

    $actualizacionData = [
        'correo' => 'correo@actualizado.com',
        'nombre' => 'Usuario Actualizado',  
        'tipo' => 'user',  
    ];

   $response = $this->putJson(route('usuarios.actualizarUsuario', ['id' => $usuario->id]), $actualizacionData);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Usuario actualizado exitosamente',
        ])
        ->assertJsonPath('data.correo', 'correo@actualizado.com');

    $this->assertDatabaseHas('usuarios', ['correo' => '']);
   
  
  
}

/** @test */
public function puede_eliminar_un_usuario(){
    $usuario = usuariosModel::factory()->create();

    $response = $this->deleteJson(route('usuarios.eliminarUsuario', ['id' => $usuario->id]));

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Usuario eliminado exitosamente',
        ]);

    $this->assertDatabaseMissing('usuarios', ['id' => $usuario->id]);
    }


    /** @test */
    public function puede_iniciar_sesion(){
        $usuario = usuariosModel::factory()->create([
            'correo' => 'correo@ejemplo.com',
            'contraseña' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $loginData = [
            'correo' => 'correo@ejemplo.com',
            'contraseña' => 'password123',
        ];
            
        $response = $this->postJson(route('usuarios.iniciarSesion'), $loginData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Inicio de sesión exitoso',
            ])
            ->assertJsonPath('data.usuario.correo', 'correo@ejemplo.com');

         
    }
    /** @test */
    public function puede_cerrar_sesion(){
        $usuario = usuariosModel::factory()->create();

        $response = $this->postJson(route('usuarios.cerrarSesion', ['id' => $usuario->id]));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Cierre de sesión exitoso',
            ]);
    }   

   /** @test */
   public function puede_crear_usuario_con_datos_invalidos(){
        $usuarioData = [
            'correo' => 'correo-invalido',
            'nombre' => '',
            'contraseña' => 'short',
            'tipo' => 'unknown',
        ];

        $response = $this->postJson(route('usuarios.registrarUsuario'), $usuarioData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['correo', 'nombre', 'contraseña', 'tipo']);
    }
   
} 
?>