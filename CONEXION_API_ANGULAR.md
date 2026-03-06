# Conexión Laravel API con Angular - Control de Horario

## 🔧 Configuración Completada

### Backend (Laravel)

#### 1. Rutas API configuradas
Las siguientes rutas están disponibles en `/api`:

**Rutas Públicas:**
- `GET /api/health` - Verificar estado de la API

**Rutas de Autenticación (públicas):**
- `POST /login` - Iniciar sesión
- `POST /register` - Registrar usuario
- `POST /logout` - Cerrar sesión (requiere autenticación)

**Rutas Protegidas (requieren token de autenticación):**
- `/api/user` - Obtener usuario actual
- `/api/users` - CRUD de usuarios
- `/api/horarios` - CRUD de horarios
- `/api/modalidades` - CRUD de modalidades
- `/api/workplaces` - CRUD de lugares de trabajo
- `/api/trabajadores` - CRUD de trabajadores
- `/api/departamentos` - CRUD de departamentos
- `/api/jefes` - CRUD de jefes
- `/api/roles` - CRUD de roles
- `/api/secuencias` - CRUD de secuencias
- `/api/logs` - Consulta de logs

#### 2. CORS Configurado
- Frontend URL permitida: `http://localhost:4200`
- Credenciales: Habilitadas
- Headers permitidos: Todos
- Métodos permitidos: Todos

### Frontend (Angular)

#### 1. Archivos Creados

**Configuración de Entorno:**
- `src/environments/environment.ts` - Configuración desarrollo
- `src/environments/environment.prod.ts` - Configuración producción

**Servicios:**
- `src/app/services/auth.service.ts` - Servicio de autenticación
- `src/app/api-service.ts` - Servicio API (actualizado)

**Interceptors:**
- `src/app/interceptors/auth.interceptor.ts` - Interceptor para agregar token JWT

**Guards:**
- `src/app/guards/auth.guard.ts` - Protección de rutas

#### 2. Configuración Aplicada
- HttpClient configurado con interceptor de autenticación
- Variables de entorno para URLs de API
- Sistema de autenticación con JWT

## 🚀 Cómo Usar

### 1. Iniciar Backend Laravel

```bash
cd laravel-api

# Instalar dependencias (si es necesario)
composer install

# Configurar base de datos en .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=punctual_db
# DB_USERNAME=proyecto_user
# DB_PASSWORD=yv3gpgUBgUZN3Cdd

# Ejecutar migraciones
php artisan migrate

# Iniciar servidor
php artisan serve
```

La API estará disponible en: `http://localhost:8000`

### 2. Iniciar Frontend Angular

```bash
cd ControlHorario/controlHorario

# Instalar dependencias (si es necesario)
npm install

# Iniciar servidor de desarrollo
npm start
# o
ng serve
```

La aplicación estará disponible en: `http://localhost:4200`

## 📝 Ejemplos de Uso

### Autenticación

```typescript
import { inject } from '@angular/core';
import { AuthService } from './services/auth.service';

export class LoginComponent {
  private authService = inject(AuthService);

  login() {
    this.authService.login('user@example.com', 'password').subscribe({
      next: (response) => {
        console.log('Login exitoso:', response);
        // El token se guarda automáticamente
      },
      error: (error) => {
        console.error('Error en login:', error);
      }
    });
  }

  logout() {
    this.authService.logout();
  }
}
```

### Llamadas a la API

```typescript
import { inject } from '@angular/core';
import { ApiService } from './api-service';

export class HorariosComponent {
  private apiService = inject(ApiService);

  loadHorarios() {
    this.apiService.getHorarios().subscribe({
      next: (horarios) => {
        console.log('Horarios:', horarios);
      },
      error: (error) => {
        console.error('Error:', error);
      }
    });
  }

  createHorario(data: any) {
    this.apiService.createHorario(data).subscribe({
      next: (response) => {
        console.log('Horario creado:', response);
      },
      error: (error) => {
        console.error('Error:', error);
      }
    });
  }
}
```

### Proteger Rutas

```typescript
// app.routes.ts
import { Routes } from '@angular/router';
import { authGuard } from './guards/auth.guard';

export const routes: Routes = [
  { path: 'login', component: LoginComponent },
  { 
    path: 'horarios', 
    component: HorariosComponent,
    canActivate: [authGuard] // Ruta protegida
  },
  // ... más rutas
];
```

## 🔐 Flujo de Autenticación

1. **Login:** El usuario se autentica con email y contraseña
2. **Token:** Laravel devuelve un token JWT que se guarda en `localStorage`
3. **Interceptor:** Todas las peticiones HTTP incluyen automáticamente el token en el header `Authorization: Bearer {token}`
4. **Guards:** Las rutas protegidas verifican si el usuario está autenticado
5. **Logout:** Se elimina el token y se redirige al login

## 🔍 Verificar Conexión

### 1. Probar endpoint de salud
```bash
curl http://localhost:8000/api/health
```

Respuesta esperada:
```json
{
  "status": "ok",
  "message": "API is running"
}
```

### 2. Probar login desde Angular
```typescript
// En la consola del navegador
fetch('http://localhost:8000/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    email: 'test@example.com',
    password: 'password'
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

## ⚠️ Notas Importantes

1. **CSRF Protection:** Laravel Sanctum maneja CSRF automáticamente para SPAs
2. **Credenciales:** Asegúrate de que las credenciales de la base de datos en `.env` sean correctas
3. **Migraciones:** Ejecuta `php artisan migrate` antes de usar la API
4. **Seeder:** Considera crear un seeder para datos de prueba

## 🛠️ Solución de Problemas

### Error CORS
- Verifica que `FRONTEND_URL=http://localhost:4200` esté en el archivo `.env` de Laravel
- Reinicia el servidor de Laravel después de cambiar `.env`

### Error 401 Unauthorized
- Verifica que el token esté siendo enviado correctamente
- Comprueba que el token no haya expirado
- Asegúrate de que las rutas protegidas usen el middleware `auth:sanctum`

### No se guardan los datos
- Verifica que las migraciones se hayan ejecutado
- Comprueba los logs de Laravel en `storage/logs/laravel.log`
- Revisa la consola del navegador para errores

## 📚 Recursos

- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Angular HttpClient](https://angular.dev/guide/http)
- [Angular Guards](https://angular.dev/guide/routing/common-router-tasks#preventing-unauthorized-access)

---

✅ **La conexión entre Laravel API y Angular está completamente configurada y lista para usar.**
