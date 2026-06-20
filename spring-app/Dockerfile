# --- Etapa 1: build con Maven ---
FROM maven:3.9-eclipse-temurin-21 AS build
WORKDIR /app
COPY pom.xml .
# Descarga dependencias primero (capa cacheable): si solo cambia el codigo
# fuente despues, Docker no vuelve a descargar todo Maven Central.
RUN mvn dependency:go-offline -B
COPY src ./src
RUN mvn package -DskipTests

# --- Etapa 2: imagen final, solo con el JRE y el .jar ---
FROM eclipse-temurin:21-jre-jammy
WORKDIR /app
COPY --from=build /app/target/*.jar app.jar
EXPOSE 8090
ENTRYPOINT ["java", "-jar", "app.jar"]
