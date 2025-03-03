Aquí tienes la documentación del cambio realizado en el **TribusController**, incluyendo la eliminación de referencias a "leeche", la modificación de migraciones, seeders y rutas.

---

## **Documentación del Cambio en TribusController**

### **Objetivo del Cambio**
El objetivo de este cambio fue **eliminar la referencia a "leeche"** en la estructura de datos del proyecto. Para lograrlo, se realizaron modificaciones en:
- **Controlador:** `TribusController.php`
- **Migraciones:** `create_users_miembros_table.php`
- **Seeder:** `UsersMiembrosTableSeeder.php`
- **Ruta del endpoint de tribus:** Se modificó la ruta de `DetalleController` a `TribusController`.

---

### **1. Cambios en el Controlador `TribusController.php`**
Se eliminó toda referencia a "leeche", lo que implicó:
- Eliminación de consultas relacionadas con `leeche_cliente_id`.
- Eliminación de cálculos de compras asociadas a "leeche".
- Mantenimiento de la estructura de datos de tribus sin dependencias externas.

#### **Código Anterior**
El código anterior contenía consultas relacionadas con "leeche":
```php
$miembros = DB::table('users_miembros')
    ->select('lider', 'id_user', 'leeche_cliente_id', 'comercial', 'inicio', 'fin', 'estado')
    ->selectRaw("(SELECT GROUP_CONCAT(id) FROM leeche_2021.users where id_user_referido = users_miembros.leeche_cliente_id) as referidos")
    ->whereIn('id_user',$tribus->pluck('id'))
    ->orderBy('cant_participaciones','DESC')
    ->get();
```
También se realizaban cálculos con base en compras de "leeche":
```php
$comprasMes = DB::table('leeche_2021.pedidos')
    ->selectRaw("SUM(valor_productos - valor_descuento + valor_impuestos) as total")
    ->whereIn('estado',[4,31,32,33,34])
    ->whereIn('created_by',$referidos)
    ->whereBetween('entrega_fecha',[$mes['inicio'],$mes['fin']])
    ->whereBetween('entrega_fecha',[$value->inicio,$value->fin])
    ->get()
    ->sum('total') / 1000;
```

#### **Código Nuevo**
Se eliminó cualquier referencia a "leeche" y sus cálculos de compras. Ahora el código se centra únicamente en la estructura de tribus sin elementos externos:
```php
$miembros = DB::table('users_miembros')
    ->select('lider', 'id_user', 'comercial', 'inicio', 'fin', 'estado')
    ->selectRaw("CONCAT_WS(' - ',nombre,empresa) as nombre_completo")
    ->selectRaw("(SELECT IFNULL(SUM(puntos),0) FROM retos_miembros where id_miembro = users_miembros.id) cant_participaciones")
    ->whereIn('id_user', $tribus->pluck('id'))
    ->orderBy('cant_participaciones', 'DESC')
    ->get();
```

---

### **2. Cambios en la Migración `create_users_miembros_table.php`**
La columna **`leeche_cliente_id`** fue eliminada de la estructura de la tabla.

#### **Migración Anterior**
```php
$table->string('leeche_cliente_id')->nullable();
```

#### **Migración Nueva**
```php
// Se eliminó la columna `leeche_cliente_id`
```

---

### **3. Cambios en el Seeder `UsersMiembrosTableSeeder.php`**
Se eliminó la dependencia de `leeche_cliente_id`, ya que los miembros ahora solo se asocian directamente a tribus.

#### **Seeder Anterior**
```php
DB::table('users_miembros')->insert([
    'nombre' => 'Miembro 1 Tribu ' . $tribuId,
    'empresa' => 'Empresa 1',
    'lider' => false,
    'comercial' => false,
    'inicio' => now(),
    'estado' => 1,
    'id_user' => $tribuId,
    'leeche_cliente_id' => Str::random(10), // Eliminado
    'created_at' => now(),
    'updated_at' => now()
]);
```

#### **Seeder Nuevo**
```php
DB::table('users_miembros')->insert([
    'nombre' => 'Miembro 1 Tribu ' . $tribuId,
    'empresa' => 'Empresa 1',
    'lider' => false,
    'comercial' => false,
    'inicio' => now(),
    'estado' => 1,
    'id_user' => $tribuId,
    'created_at' => now(),
    'updated_at' => now()
]);
```

---

### **4. Cambios en la Ruta del Endpoint**
Se modificó la ruta que contenía el **endpoint principal de tribus**. Anteriormente, `DetalleController` gestionaba los datos de tribus, ahora ha sido sustituido por `TribusController`.

#### **Ruta Anterior**
```php
use App\Http\Controllers\Tribus\DetalleController;

Route::group(['prefix' => 'tribus/detalle'], function() {
    Route::get('datos/{idTribu}', [DetalleController::class, 'datos']);
});
```

#### **Nueva Ruta**
```php
use App\Http\Controllers\Home\TribusController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'tribus/detalle'], function() {
    Route::get('datos/{idTribu}', [TribusController::class, 'getTribus']);
});
```

### **5. Conclusión**
Estos cambios eliminan completamente la dependencia del sistema "leeche", haciendo que las tribus sean autogestionadas sin datos de compras o referencias externas. Además, la ruta ha sido centralizada en el `TribusController` para mayor claridad en la organización del código.

---

Si necesitas algún otro detalle o ajuste en la documentación, avísame. 🚀