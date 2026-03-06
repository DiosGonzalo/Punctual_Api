# Guía de Solución de Problemas - Conexión API

## 🔍 Diagnóstico

### 1. Verificar que Laravel esté corriendo

```bash
cd laravel-api
php artisan serve
```

Deberías ver: `Server started on http://127.0.0.1:8000`

### 2. Verificar conexión a la base de datos

```bash
cd laravel-api
php check-db.php
```

Este script verificará:
- ✅ Conexión a la base de datos
- ✅ Tablas existentes
- ✅ Cantidad de registros por tabla

### 3. Si no hay tablas, ejecutar migraciones

```bash
cd laravel-api
php artisan migrate
```

### 4. Probar endpoint de salud

Abre en tu navegador o usa curl:
```bash
curl http://localhost:8000/api/health
```

Respuesta esperada:
```json
{"status":"ok","message":"API is running"}
```

### 5. Probar crear un usuario

```bash
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nombre": "Test",
    "apellidos": "User",
    "email": "test@test.com",
    "telefono": "123456789",
    "password": "password123",
    "is_presente": false
  }'
```

## ⚠️ Errores Comunes y Soluciones

### Error: "No se puede conectar con el servidor"

**Causa:** Laravel no está corriendo
**Solución:** 
```bash
cd laravel-api
php artisan serve
```

### Error: "SQLSTATE[HY000] [1049] Unknown database"

**Causa:** La base de datos no existe
**Solución:**
1. Crear la base de datos en MySQL:
```sql
CREATE DATABASE punctual_db;
```

2. O actualizar `.env` con una base de datos que exista

### Error: "SQLSTATE[HY000] [2002] Connection refused"

**Causa:** MySQL no está corriendo o configuración incorrecta
**Solución:**
1. Iniciar MySQL/XAMPP/WAMP
2. Verificar credenciales en `.env`:
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=punctual_db
DB_USERNAME=proyecto_user
DB_PASSWORD=yv3gpgUBgUZN3Cdd
```

### Error: "Base integrity constraint violation"

**Causa:** Intentando crear registros con referencias a tablas vacías
**Solución:**
1. Crear primero los datos de referencia (roles, departamentos, etc.)
2. Usar campos nullable: `id_rol`, `id_horario`, etc. pueden ser null

### Error 422: "Validation errors"

**Causa:** Datos enviados no cumplen con las validaciones
**Solución:** Ver consola del navegador para detalles específicos. El interceptor ahora muestra los errores de validación.

## 🧪 Componente de Prueba

He creado un componente para probar la conexión. Agrégalo a tus rutas:

```typescript
// app.routes.ts
import { TestConnectionComponent } from './components/test-connection.component';

export const routes: Routes = [
  { path: 'test', component: TestConnectionComponent },
  // ... otras rutas
];
```

Navega a `http://localhost:4200/test` para:
- ✅ Verificar estado de la API
- ✅ Ver datos existentes (muestra 0 si no hay)
- ✅ Crear datos de prueba
- ✅ Ver errores detallados

## 📋 Checklist de Verificación

Antes de reportar un error, verifica:

- [ ] Laravel está corriendo (`php artisan serve`)
- [ ] MySQL está corriendo
- [ ] Base de datos existe
- [ ] Migraciones ejecutadas (`php artisan migrate`)
- [ ] `.env` tiene credenciales correctas
- [ ] Angular está corriendo (`ng serve`)
- [ ] No hay errores en la consola del navegador (F12)
- [ ] URL de la API es correcta en `environment.ts`

## 🔧 Comandos Útiles

### Laravel
```bash
# Ver logs de Laravel
tail -f storage/logs/laravel.log

# Limpiar caché
php artisan cache:clear
php artisan config:clear

# Ver rutas
php artisan route:list

# Crear base de datos y migrar
php artisan migrate:fresh
```

### Angular
```bash
# Ver en modo detallado
ng serve --verbose

# Limpiar caché
rm -rf .angular/cache
npm run build
```

## 📞 Estado Actual

✅ **Configuración completada:**
- Rutas API públicas para desarrollo
- Interceptor de errores con mensajes claros
- Componente de prueba con botones para crear datos
- Arrays vacíos se muestran como "0 registros"
- Manejo de errores mejorado

⚠️ **Rutas públicas activas (REMOVER EN PRODUCCIÓN):**
- GET/POST/PUT/DELETE para todas las entidades
- Solo para facilitar desarrollo sin autenticación
