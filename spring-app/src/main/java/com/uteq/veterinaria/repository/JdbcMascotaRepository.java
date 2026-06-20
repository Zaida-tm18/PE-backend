package com.uteq.veterinaria.repository;

import com.uteq.veterinaria.model.Mascota;
import org.springframework.stereotype.Repository;

import javax.sql.DataSource;
import java.sql.Connection;
import java.sql.Date;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Types;
import java.util.ArrayList;
import java.util.List;
import java.util.Optional;

/**
 * Implementacion JDBC del CRUD de Mascota. Equivalente Java de
 * PdoMascotaRepository.php: mismas 5 operaciones, mismo principio de
 * "ningun dato del usuario se concatena en el SQL".
 */
@Repository
public class JdbcMascotaRepository implements MascotaRepository {

    private final DataSource dataSource;

    public JdbcMascotaRepository(DataSource dataSource) {
        this.dataSource = dataSource;
    }

    @Override
    public List<Mascota> listarTodas() {
        String sql = "SELECT id, nombre, especie, raza, fecha_nacimiento, propietario_nombre, "
            + "propietario_telefono, usuario_id FROM mascotas ORDER BY creado_en DESC";

        List<Mascota> mascotas = new ArrayList<>();

        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                mascotas.add(mapearMascota(rs));
            }
        } catch (SQLException e) {
            throw new RuntimeException("Error al listar mascotas", e);
        }

        return mascotas;
    }

    @Override
    public Optional<Mascota> buscarPorId(int id) {
        String sql = "SELECT id, nombre, especie, raza, fecha_nacimiento, propietario_nombre, "
            + "propietario_telefono, usuario_id FROM mascotas WHERE id = ? LIMIT 1";

        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setInt(1, id);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return Optional.of(mapearMascota(rs));
                }
            }
        } catch (SQLException e) {
            throw new RuntimeException("Error al buscar mascota por id", e);
        }

        return Optional.empty();
    }

    @Override
    public int crear(Mascota mascota) {
        String sql = "INSERT INTO mascotas "
            + "(nombre, especie, raza, fecha_nacimiento, propietario_nombre, propietario_telefono, usuario_id) "
            + "VALUES (?, ?, ?, ?, ?, ?, ?)";

        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql, new String[] {"id"})) {

            asignarParametros(stmt, mascota);
            stmt.executeUpdate();

            try (ResultSet generatedKeys = stmt.getGeneratedKeys()) {
                if (generatedKeys.next()) {
                    return generatedKeys.getInt(1);
                }
            }

            return 0;
        } catch (SQLException e) {
            throw new RuntimeException("Error al crear mascota", e);
        }
    }

    @Override
    public boolean actualizar(int id, Mascota mascota) {
        String sql = "UPDATE mascotas SET nombre = ?, especie = ?, raza = ?, fecha_nacimiento = ?, "
            + "propietario_nombre = ?, propietario_telefono = ? WHERE id = ?";

        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            asignarParametros(stmt, mascota);
            stmt.setInt(7, id);

            return stmt.executeUpdate() > 0;
        } catch (SQLException e) {
            throw new RuntimeException("Error al actualizar mascota", e);
        }
    }

    @Override
    public boolean eliminar(int id) {
        String sql = "DELETE FROM mascotas WHERE id = ?";

        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setInt(1, id);

            return stmt.executeUpdate() > 0;
        } catch (SQLException e) {
            throw new RuntimeException("Error al eliminar mascota", e);
        }
    }

    /** Asigna los primeros 6 parametros compartidos por INSERT y UPDATE. */
    private void asignarParametros(PreparedStatement stmt, Mascota mascota) throws SQLException {
        stmt.setString(1, mascota.getNombre());
        stmt.setString(2, mascota.getEspecie());
        stmt.setString(3, mascota.getRaza());

        if (mascota.getFechaNacimiento() != null) {
            stmt.setDate(4, Date.valueOf(mascota.getFechaNacimiento()));
        } else {
            stmt.setNull(4, Types.DATE);
        }

        stmt.setString(5, mascota.getPropietarioNombre());
        stmt.setString(6, mascota.getPropietarioTelefono());

        // El septimo parametro (usuario_id en INSERT, id en UPDATE) lo
        // asigna cada metodo segun corresponda.
        if (mascota.getUsuarioId() != null) {
            stmt.setInt(7, mascota.getUsuarioId());
        }
    }

    private Mascota mapearMascota(ResultSet rs) throws SQLException {
        Mascota mascota = new Mascota();
        mascota.setId(rs.getInt("id"));
        mascota.setNombre(rs.getString("nombre"));
        mascota.setEspecie(rs.getString("especie"));
        mascota.setRaza(rs.getString("raza"));

        Date fecha = rs.getDate("fecha_nacimiento");
        mascota.setFechaNacimiento(fecha != null ? fecha.toLocalDate() : null);

        mascota.setPropietarioNombre(rs.getString("propietario_nombre"));
        mascota.setPropietarioTelefono(rs.getString("propietario_telefono"));
        mascota.setUsuarioId(rs.getInt("usuario_id"));

        return mascota;
    }
}
