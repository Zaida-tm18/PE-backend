package com.uteq.veterinaria.service;

import com.uteq.veterinaria.model.Mascota;
import com.uteq.veterinaria.repository.MascotaRepository;
import org.springframework.stereotype.Service;

import java.time.LocalDate;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.Optional;

/**
 * Logica de negocio del CRUD de Mascota. Equivalente Java de
 * MascotaController.php (la version PHP, no confundir con el controlador
 * web de Spring que recibe HTTP requests).
 */
@Service
public class MascotaService {

    private static final List<String> ESPECIES_VALIDAS =
        Arrays.asList("Perro", "Gato", "Ave", "Conejo", "Reptil", "Otro");

    private final MascotaRepository mascotaRepository;

    public MascotaService(MascotaRepository mascotaRepository) {
        this.mascotaRepository = mascotaRepository;
    }

    public static class ResultadoOperacion {
        public final boolean exito;
        public final List<String> errores;

        public ResultadoOperacion(boolean exito, List<String> errores) {
            this.exito = exito;
            this.errores = errores;
        }
    }

    public List<Mascota> listar() {
        return mascotaRepository.listarTodas();
    }

    public Optional<Mascota> obtener(int id) {
        return mascotaRepository.buscarPorId(id);
    }

    public ResultadoOperacion crear(Mascota mascota) {
        List<String> errores = validar(mascota);

        if (!errores.isEmpty()) {
            return new ResultadoOperacion(false, errores);
        }

        mascotaRepository.crear(mascota);
        return new ResultadoOperacion(true, errores);
    }

    public ResultadoOperacion actualizar(int id, Mascota mascota) {
        if (mascotaRepository.buscarPorId(id).isEmpty()) {
            return new ResultadoOperacion(false, List.of("La mascota no existe."));
        }

        List<String> errores = validar(mascota);

        if (!errores.isEmpty()) {
            return new ResultadoOperacion(false, errores);
        }

        mascotaRepository.actualizar(id, mascota);
        return new ResultadoOperacion(true, errores);
    }

    public boolean eliminar(int id) {
        return mascotaRepository.eliminar(id);
    }

    private List<String> validar(Mascota mascota) {
        List<String> errores = new ArrayList<>();

        String nombre = mascota.getNombre() == null ? "" : mascota.getNombre().trim();
        String propietario = mascota.getPropietarioNombre() == null ? "" : mascota.getPropietarioNombre().trim();

        if (nombre.isEmpty() || nombre.length() > 100) {
            errores.add("El nombre de la mascota es obligatorio (max. 100 caracteres).");
        }

        if (mascota.getEspecie() == null || !ESPECIES_VALIDAS.contains(mascota.getEspecie())) {
            errores.add("Seleccione una especie valida.");
        }

        if (propietario.isEmpty() || propietario.length() > 150) {
            errores.add("El nombre del propietario es obligatorio (max. 150 caracteres).");
        }

        if (mascota.getFechaNacimiento() != null && mascota.getFechaNacimiento().isAfter(LocalDate.now())) {
            errores.add("La fecha de nacimiento no puede ser futura.");
        }

        return errores;
    }
}
