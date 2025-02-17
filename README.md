# Hunters Campus (Backend)

Con el objetivo de retomar el proyecto y dotarlo de escalabilidad. A continuación, se describen los aspectos clave del sistema, incluyendo estructura de la base de datos, migraciones, autenticación, endpoints de la API, comandos de desarrollo, pendientes y recomendaciones.

---

## 1. Migraciones Implementadas

Las migraciones se encuentran en el directorio `database/migrations/` y se estructuran de la siguiente manera:

1. **create_users_table.php**
    
    Crea la tabla de usuarios:
    
    ```php
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('correo')->unique();
        $table->string('password');
        $table->string('color')->nullable();
        $table->string('logo')->nullable();
        $table->tinyInteger('tipo')->default(1);
        $table->timestamps();
        $table->softDeletes();
    });
    
    ```
    
2. **create_retos_table.php**
    
    Define la tabla de retos:
    
    ```php
    Schema::create('retos', function (Blueprint $table) {
        $table->id();
        $table->string('titulo', 60);
        $table->text('descripcion');
        $table->date('fecha');
        $table->time('hora');
        $table->string('lugar', 40);
        $table->integer('cantidad');
        $table->integer('puntos');
        $table->tinyInteger('estado')->default(1);
        // Llaves foráneas
        $table->foreignId('created_by')->constrained('users');
        $table->foreignId('id_user_2')->nullable()->constrained('users');
        $table->foreignId('id_user_3')->nullable()->constrained('users');
        $table->foreignId('id_user_4')->nullable()->constrained('users');
        $table->timestamps();
        $table->softDeletes();
    });
    
    ```
    
3. **create_torneos_table.php**
4. **create_users_miembros_table.php**
5. **create_users_puntos_table.php**
6. **create_retos_miembros_table.php**
7. **create_retos_comentarios_table.php**
8. **create_torneos_comentarios_table.php**
9. **create_torneos_puntos_table.php**

---

## 2. Seeders Implementados

Los seeders se encuentran en el directorio `database/seeds/` y siguen la estructura de Laravel < 8.0:

1. **DatabaseSeeder.php**
    
    Seeder principal que orquesta la ejecución de los siguientes seeders:
    
    ```php
    class DatabaseSeeder extends Seeder
    {
        public function run()
        {
            $this->call([
                UsersTableSeeder::class,
                UsersMiembrosTableSeeder::class,
                RetosTableSeeder::class,
                TorneosTableSeeder::class,
                // ...otros seeders
            ]);
        }
    }
    
    ```
    
2. **UsersTableSeeder.php**
    - Crea el usuario admin y las tribus iniciales.
    - Establece correos y contraseñas de prueba.
3. **UsersMiembrosTableSeeder.php**
    - Genera miembros para cada tribu, asignando líderes y miembros regulares.
4. **RetosTableSeeder.php**
    - Genera retos de prueba y asigna participantes y estados aleatorios.
5. **TorneosTableSeeder.php**
    - Crea torneos de ejemplo con fechas y descripciones definidas.
6. **UsersPuntosTableSeeder.php**
    - Simula puntuaciones históricas y la participación en retos y torneos.
7. **RetosMiembrosTableSeeder.php**
8. **RetosComentariosTableSeeder.php**
9. **TorneosComentariosTableSeeder.php**
10. **TorneosPuntosTableSeeder.php**

---

## 3. Estructura de la Base de Datos

### 3.1 Relaciones Principales

- **Users** → **Users_Miembros**: 1:N
- **Users** → **Retos**: 1:N
- **Users** → **Torneos**: 1:N
- **Users** → **Users_Puntos**: 1:N
- **Retos** → **Retos_Comentarios**: 1:N
- **Torneos** → **Torneos_Comentarios**: 1:N
- **Users_Miembros** → **Retos_Miembros**: 1:N

### 3.2 Diagrama ER

https://claude.site/artifacts/69d6a17e-4789-4f01-aa82-3818290daa4a 

```mermaid
erDiagram
    USERS ||--o{ USERS_MIEMBROS : "tiene"
    USERS ||--o{ RETOS : "crea/participa"
    USERS ||--o{ TORNEOS : "crea"
    USERS ||--o{ USERS_PUNTOS : "tiene"
    RETOS ||--o{ RETOS_COMENTARIOS : "tiene"
    RETOS ||--o{ RETOS_MIEMBROS : "tiene"
    TORNEOS ||--o{ TORNEOS_COMENTARIOS : "tiene"
    TORNEOS ||--o{ TORNEOS_PUNTOS : "tiene"
    USERS_MIEMBROS ||--o{ RETOS_MIEMBROS : "participa"

    USERS {
        int id PK
        string nombre
        string correo UK
        string password
        string color
        string logo
        int tipo
        datetime created_at
        datetime updated_at
        datetime deleted_at
    }

    RETOS {
        int id PK
        string titulo
        text descripcion
        date fecha
        time hora
        string lugar
        int cantidad
        int puntos
        int estado
        int created_by FK
        int id_user_2 FK
        int id_user_3 FK
        int id_user_4 FK
        int id_user_ganador FK
        datetime created_at
        datetime updated_at
        datetime deleted_at
    }

    TORNEOS {
        int id PK
        string titulo
        text descripcion
        date fecha
        int estado
        int created_by FK
        datetime created_at
        datetime updated_at
        datetime deleted_at
    }

    USERS_MIEMBROS {
        int id PK
        string nombre
        string empresa
        boolean lider
        string leeche_cliente_id
        boolean comercial
        date inicio
        date fin
        int estado
        int id_user FK
        datetime created_at
        datetime updated_at
        datetime deleted_at
    }

    USERS_PUNTOS {
        int id PK
        int tipo
        int puntos_afectado
        int puntos_anteriores
        int puntos_nuevos
        int afectacion
        string manual_nombre
        text manual_descripcion
        int id_user FK
        int id_reto FK
        int id_torneo FK
        int created_by FK
        datetime created_at
        datetime updated_at
        datetime deleted_at
    }

    RETOS_MIEMBROS {
        int id PK
        int puntos
        text comentario
        int id_miembro FK
        int id_reto FK
        int id_torneo FK
        int created_by FK
        datetime created_at
        datetime updated_at
        datetime deleted_at
    }

    RETOS_COMENTARIOS {
        int id PK
        text comentario
        int id_reto FK
        int created_by FK
        datetime created_at
        datetime updated_at
        datetime deleted_at
    }

    TORNEOS_COMENTARIOS {
        int id PK
        text comentario
        int id_torneo FK
        int created_by FK
        datetime created_at
        datetime updated_at
        datetime deleted_at
    }

    TORNEOS_PUNTOS {
        int id PK
        int puntos
        int juegos
        int victorias
        int id_torneo FK
        int id_user FK
        int created_by FK
        int updated_by FK
        datetime created_at
        datetime updated_at
        datetime deleted_at
    }

```

[er-diagram.mermaid](attachment:74a52da7-a6e7-467f-bbd6-7b1b09724d3e:er-diagram.mermaid)

---

## 4. Sistema de Autenticación

### 4.1 Implementación JWT en el Backend

El backend cuenta con un sistema de autenticación basado en JWT (`tymon/jwt-auth`), permitiendo el manejo de sesiones seguras. Se configura en `.env` mediante `JWT_SECRET`.

### 4.2 Limitaciones en el Frontend

A pesar de que JWT está implementado en el backend, **no se está utilizando en el frontend**. En su lugar, el frontend solicita una contraseña al usuario cuando se requiere realizar cambios como agregar un torneo. Esta contraseña se compara directamente con cualquier registro en la base de datos sin ningún tipo de hash o encriptación, lo que representa un problema de seguridad significativo.

### 4.3 Endpoints de Autenticación

- **Login con correo:**
    
    ```
    POST /api/auth/login
    Content-Type: application/json
    {
        "correo": "admin@campus.com",
        "password": "admin123"
    }
    ```
    
- **Pedir contraseña (Verificacion que se usa en el fronend):**
    
    ```
    POST /api/auth/pedir-password
    Content-Type: application/json
    {
        "password": "alpha123"
    }
    ```
    

```
POST /api/auth/login       # Autenticación con correo y password
POST /api/auth/pedir-password  # Verificación mediante password

```

### 4.2 Códigos de Estado

- **200:** Éxito
- **400:** Credenciales inválidas
- **401:** No autorizado
- **500:** Error del servidor

---

## 5. Comandos de Desarrollo

### 5.1 Instalación Inicial

```bash
composer install
php artisan key:generate
php artisan jwt:secret

```

### 5.2 Gestión de la Base de Datos

```bash
php artisan migrate          # Ejecuta las migraciones
php artisan migrate:fresh    # Recrea la base de datos desde cero
php artisan db:seed          # Pobla la base de datos con datos de prueba
composer dump-autoload       # Actualiza el autoloader

```

---

## 6. Pendientes de Implementación

1. Integrar carga de archivos a S3.
2. Optimizar Manejo del ORM agregando las relaciones en los modelos
3. Implementar un JWT seguro desde el fronent y en todas las rutas excepto el login y register
4. Documentar endpoints adicionales.