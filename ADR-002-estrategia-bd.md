# ADR-002: Estrategia de base de datos

## Estado
Aceptado

## Contexto
El sistema necesita persistir usuarios (para autenticación) y mascotas (entidad del CRUD). Ambas tecnologías de backend (PHP y Spring Boot) deben operar sobre los mismos datos para que la comparación entre ellas sea válida y no esté sesgada por diferencias en el modelo de datos.

## Decisión
1. **Una sola base de datos MySQL 8.0 compartida** entre las dos aplicaciones backend, definida una única vez en `php-app/sql/schema.sql` y montada como script de inicialización del contenedor `mysql_db`. Ni PHP ni Spring Boot generan o migran el esquema por sí mismos (no se usó `php artisan migrate` ni Hibernate `ddl-auto=update`), precisamente para que ambas tecnologías lean/escriban la misma estructura sin divergencias.
2. **Contraseñas almacenadas como hash, nunca en texto plano**: `password_hash()` con Argon2id en PHP, `BCryptPasswordEncoder` en Spring Boot. Ambas son funciones de hashing lentas y con sal automática, diseñadas específicamente para contraseñas (a diferencia de SHA-256/MD5, que son rápidas y por tanto vulnerables a ataques de fuerza bruta con GPU).
3. **Acceso a datos exclusivamente mediante prepared statements** (PDO en PHP, JDBC en Java), nunca por concatenación de strings, para eliminar la inyección SQL (A03 del OWASP Top 10) en su origen, no solo mitigarla con saneamiento posterior.
4. **Connection pooling**: en Spring Boot, el pool lo gestiona automáticamente HikariCP (incluido en `spring-boot-starter-jdbc`); en PHP/PDO no hay pool nativo entre peticiones HTTP (cada request de PHP-FPM abre su propia conexión y la cierra al terminar), lo cual es una diferencia arquitectónica real entre ambos stacks y se documenta como tal en la tabla comparativa, no se intenta "igualar" artificialmente.

## Alternativas consideradas
- **Una base de datos por tecnología** (una para PHP, otra para Spring Boot): descartada porque introduciría el riesgo de que ambas bases de datos divergieran en estructura o contenido, invalidando la comparación pedida en el Paso 4 de la guía ("ambas aplicaciones tienen el mismo módulo CRUD funcionando").
- **ORM con migraciones automáticas** (Eloquent en PHP, Hibernate `ddl-auto` en Spring): descartado por la misma razón que en ADR-001: oculta el SQL real y dificulta justificar técnicamente las decisiones de seguridad tomadas a nivel de consulta.

## Consecuencias
- **Positivas**: cualquier dato creado desde la app PHP es visible de inmediato desde la app Spring Boot y viceversa, lo que permite demostrar en el video/demo del PFC que ambos backends son intercambiables sobre el mismo dominio de datos.
- **Negativas**: al compartir esquema, un cambio futuro en la tabla `mascotas` (por ejemplo, agregar una columna) debe replicarse manualmente en el código de mapeo de **ambos** repositorios (`PdoMascotaRepository.php` y `JdbcMascotaRepository.java`), ya que no hay una única fuente de verdad a nivel de código (solo a nivel de SQL).
