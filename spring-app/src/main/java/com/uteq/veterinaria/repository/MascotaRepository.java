package com.uteq.veterinaria.repository;

import com.uteq.veterinaria.model.Mascota;

import java.util.List;
import java.util.Optional;

/**
 * Contrato del repositorio de Mascota (entidad del CRUD).
 * Equivalente Java de MascotaRepositoryInterface.php.
 */
public interface MascotaRepository {

    List<Mascota> listarTodas();

    Optional<Mascota> buscarPorId(int id);

    int crear(Mascota mascota);

    boolean actualizar(int id, Mascota mascota);

    boolean eliminar(int id);
}
