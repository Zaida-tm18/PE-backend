# Backend - Sistema de Gestión Veterinaria (PFC)

Práctica Experimental Unidad II - Aplicaciones Web

## Tecnologías
- **PHP 8.2** (autenticación + CRUD de Mascota con PDO/Repository) — puerto 8080
- **Java 21 / Spring Boot 3.2** (la misma funcionalidad con JDBC/Repository) — puerto 8090
- **PostgreeSQL** compartida por ambas apps (Docker)

## Cómo levantar el entorno (PHP + Spring Boot + PostgreSQL)

Requiere Docker Desktop instalado y corriendo.

```bash
cd veterinaria-backend
docker-compose up -d --build
```

Esto levanta 4 contenedores:
| Servicio | URL | Descripción |
|---|---|---|
| `php-app` | http://localhost:8080 | Aplicación PHP |
| `spring-app` | http://localhost:8090 | Aplicación Spring Boot (misma funcionalidad) |
| `postgres_db` | localhost:5432 | Base de datos compartida por ambas apps |
| `adminer` | http://localhost:8081 | Cliente web para ver la BD (sistema: PostgreSQL, servidor: `postgres_db`, usuario: `vet_user` / clave: `vet_pass_2026`) |

> El primer `docker-compose up --build` puede tardar varios minutos: Maven descarga todas las dependencias de Spring Boot dentro del contenedor. Las siguientes veces será mucho más rápido gracias al cache de capas de Docker.

### Instalar dependencias PHP (la primera vez)

```bash
docker-compose exec php-app composer install
```

Esto genera `vendor/autoload.php`, necesario para que funcione el autoload de las clases `App\...`.

## Probar el flujo completo (en cada tecnología)

**PHP (puerto 8080):**
1. Abre **http://localhost:8080/register.php** y crea una cuenta.
2. Inicia sesión en **http://localhost:8080/login.php**.
3. Verás el panel en **http://localhost:8080/index.php**.
4. Entra a "Gestionar mascotas" → Crear, Editar, Eliminar y Listar (las 5 operaciones del CRUD).
5. Cierra sesión e intenta entrar de nuevo a `/mascotas/index.php`: debe redirigirte a `/login.php`.

**Spring Boot (puerto 8090):**
1. Abre **http://localhost:8090/register** y crea una cuenta (es una cuenta independiente; cada app valida sus propias credenciales, aunque ambas leen/escriben en la misma tabla `usuarios`).
2. Inicia sesión en **http://localhost:8090/login**.
3. Verás el panel en **http://localhost:8090/**.
4. Entra a "Gestionar mascotas" → mismas 5 operaciones.
5. Cierra sesión e intenta entrar de nuevo a `/mascotas`: Spring Security te redirige solo, sin código manual de protección de ruta.

**Dato interesante**: como ambas apps comparten la misma base de datos, una mascota creada desde PHP aparece de inmediato en el listado de Spring Boot, y viceversa (recarga la página). Esto demuestra que el "mismo módulo CRUD" funciona igual sin importar la tecnología.

## Verificar que no hay inyección SQL por concatenación

- PHP: revisar `php-app/src/repositories/PdoUsuarioRepository.php` y `PdoMascotaRepository.php` — todo usa `prepare()` + `bindValue()`/`execute([...])`.
- Java: revisar `spring-app/.../repository/JdbcUsuarioRepository.java` y `JdbcMascotaRepository.java` — todo usa `PreparedStatement` con placeholders `?`.

Ningún archivo concatena datos del usuario directamente en un string SQL.

## Análisis estático con PHPStan (Paso 5.1 de la guía)

```bash
docker-compose exec php-app composer install   # si no lo has hecho aún
docker-compose exec php-app vendor/bin/phpstan analyse --configuration=phpstan.neon
```


## Estructura del proyecto

```
veterinaria-backend/
├── docker-compose.yml
├── php-app/                # Ver estructura interna en la sección anterior
├── spring-app/
│   ├── pom.xml
│   ├── Dockerfile
│   └── src/main/
│       ├── java/com/uteq/veterinaria/
│       │   ├── config/SecurityConfig.java
│       │   ├── controller/         # AuthController, HomeController, MascotaWebController
│       │   ├── model/              # Usuario, Mascota
│       │   ├── repository/         # Interfaces + implementaciones JDBC
│       │   └── service/            # RegistroService, MascotaService, UsuarioDetailsService
│       └── resources/
│           ├── application.properties
│           └── templates/          # Vistas Thymeleaf (auto-escapan XSS)
└── docs/
    ├── adr/
    │   ├── ADR-001-tecnologia-backend.md
    │   └── ADR-002-estrategia-bd.md
    └── tabla-comparativa.md        # Guía para completar la tabla comparativa con datos reales
```

## Mitigaciones OWASP implementadas

| Vulnerabilidad | PHP | Spring Boot |
|---|---|---|
| A01 - Control de acceso roto | `AuthMiddleware::requireAuth()` manual en cada ruta | `.anyRequest().authenticated()` declarativo en `SecurityConfig` |
| A02 - Fallas criptográficas | `password_hash()` Argon2id | `BCryptPasswordEncoder` |
| A03 - Inyección | PDO con prepared statements | JDBC con `PreparedStatement` |
| A05 - Config. de seguridad incorrecta | Cabeceras HTTP manuales (`Security::setSecurityHeaders()`) | Cabeceras activas por defecto + CSP/Referrer-Policy explícitas |
| A07 - Fallas de autenticación | Política de contraseña, `session_regenerate_id()`, mitigación de timing attack — todo a mano | Spring Security regenera la sesión y compara hashes en tiempo constante automáticamente |
| XSS | Saneamiento manual con `Security::e()` en cada vista | Auto-escape por defecto de Thymeleaf (`th:text`) |
| CSRF | Token por sesión generado y validado a mano | Activado por defecto por Spring Security en todo POST |

## ADRs (Architecture Decision Records)

Las decisiones arquitectónicas clave de este backend están documentadas en `docs/adr/`:
- [ADR-001: Elección de tecnologías de backend](docs/adr/ADR-001-tecnologia-backend.md)
- [ADR-002: Estrategia de base de datos](docs/adr/ADR-002-estrategia-bd.md)


