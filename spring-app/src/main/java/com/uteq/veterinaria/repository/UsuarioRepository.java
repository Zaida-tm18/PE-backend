package com.uteq.veterinaria.repository;

import com.uteq.veterinaria.model.Usuario;

import java.util.Optional;

/**
 * Contrato del repositorio de Usuario (equivalente Java de
 * UsuarioRepositoryInterface.php). El servicio de autenticacion depende de
 * esta interfaz, no de la implementacion JDBC concreta.
 */
public interface UsuarioRepository {

    Optional<Usuario> buscarPorEmail(String email);

    int crear(Usuario usuario);

    boolean existeEmail(String email);
}
