# ADR-001: Elección de tecnologías de backend

## Estado
Aceptado

## Contexto
El Proyecto Fin de Curso (Sistema de Gestión Veterinaria) requiere un backend del lado del servidor que implemente autenticación segura y un módulo CRUD, en al menos dos tecnologías, conforme al Objetivo General de la Práctica Experimental Unidad II. Las opciones evaluadas para la segunda tecnología fueron ASP.NET Core y Java/Spring Boot.

## Decisión
Se implementó el backend con **PHP 8.2** (obligatorio) como primera tecnología, y **Java 21 + Spring Boot 3.2** como segunda tecnología, ambas usando **PDO/JDBC con prepared statements** y el **patrón Repository** (interfaz + implementación concreta).

Para el acceso a datos en Spring Boot se eligió **JDBC puro** (no Spring Data JPA/Hibernate), de modo que la comparación con PHP/PDO fuera directa: ambas implementaciones controlan explícitamente la apertura de conexión, la preparación de la sentencia y el mapeo fila-a-objeto, sin un ORM intermedio que oculte esos pasos.

## Alternativas consideradas
- **ASP.NET Core**: descartado para esta entrega porque el equipo tiene más experiencia previa con la sintaxis de Java y porque Spring Boot permite reutilizar el mismo motor de base de datos (PostgreSQL) sin cambios, mientras que el ecosistema .NET tiende a integrarse de forma más nativa con SQL Server.
- **Spring Data JPA**: descartado para mantener la comparación de "verbosidad de acceso a datos" justa frente a PDO; JPA habría ocultado demasiados detalles (generación automática de SQL) que sí son visibles y comparables en PDO.

## Consecuencias
- **Positivas**: el código de los repositorios en ambas tecnologías es estructuralmente paralelo (mismas 5 operaciones CRUD, mismo uso explícito de prepared statements), lo que facilita justificar cada criterio de la tabla comparativa con evidencia de código real, no solo de documentación externa.
- **Negativas**: usar JDBC puro en lugar de JPA implica más código repetitivo (mapeo manual de `ResultSet` a objetos) del que tendría una aplicación Spring Boot "idiomática"; esto debe aclararse en el informe para no dar la impresión de que JPA es más lento o peor, solo que se evitó para esta comparación específica.
- **Seguimiento**: si el PFC evoluciona más allá de esta práctica, se recomienda evaluar Spring Data JPA para reducir código repetitivo en entidades adicionales (Cliente, Cita, Historial médico).
