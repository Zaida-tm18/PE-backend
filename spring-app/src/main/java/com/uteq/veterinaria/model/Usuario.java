package com.uteq.veterinaria.model;

/**
 * Representa un usuario del sistema (veterinario/recepcionista).
 * POJO simple: como NO usamos JPA/Hibernate (decision documentada en
 * ADR-002), esta clase es solo una estructura de datos; el mapeo
 * fila-a-objeto se hace a mano en el repositorio (ver JdbcUsuarioRepository).
 */
public class Usuario {

    private Integer id;
    private String nombre;
    private String email;
    private String passwordHash;
    private String rol;

    public Usuario() {
    }

    public Usuario(Integer id, String nombre, String email, String passwordHash, String rol) {
        this.id = id;
        this.nombre = nombre;
        this.email = email;
        this.passwordHash = passwordHash;
        this.rol = rol;
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public String getNombre() {
        return nombre;
    }

    public void setNombre(String nombre) {
        this.nombre = nombre;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getPasswordHash() {
        return passwordHash;
    }

    public void setPasswordHash(String passwordHash) {
        this.passwordHash = passwordHash;
    }

    public String getRol() {
        return rol;
    }

    public void setRol(String rol) {
        this.rol = rol;
    }
}
