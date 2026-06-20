package com.uteq.veterinaria.repository;

import com.uteq.veterinaria.model.Usuario;
import org.springframework.stereotype.Repository;

import javax.sql.DataSource;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Optional;

/**
 * Implementacion concreta con JDBC puro + PreparedStatement.
 *
 * Equivalente Java de PdoUsuarioRepository.php. La comparacion clave para
 * el informe (seccion 5.3 de la guia): aqui el manejo de conexiones es
 * EXPLICITO (try-with-resources abre y cierra Connection/PreparedStatement/
 * ResultSet en cada metodo), pero el POOL de conexiones es AUTOMATICO
 * (HikariCP, configurado solo por tener spring-boot-starter-jdbc en el
 * pom.xml). En PHP/PDO es al reves: la conexion es mas simple de abrir,
 * pero no hay pool de conexiones nativo sin agregar una extension aparte.
 */
@Repository
public class JdbcUsuarioRepository implements UsuarioRepository {

    private final DataSource dataSource;

    public JdbcUsuarioRepository(DataSource dataSource) {
        this.dataSource = dataSource;
    }

    @Override
    public Optional<Usuario> buscarPorEmail(String email) {
        String sql = "SELECT id, nombre, email, password_hash, rol FROM usuarios WHERE email = ? LIMIT 1";

        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            // El placeholder "?" es la version JDBC del ":email" de PDO:
            // el valor SIEMPRE viaja separado del texto SQL, nunca concatenado.
            stmt.setString(1, email);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return Optional.of(mapearUsuario(rs));
                }
            }
        } catch (SQLException e) {
            throw new RuntimeException("Error al buscar usuario por email", e);
        }

        return Optional.empty();
    }

    @Override
    public int crear(Usuario usuario) {
        String sql = "INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES (?, ?, ?, ?::rol_usuario)";

        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql, new String[] {"id"})) {

            stmt.setString(1, usuario.getNombre());
            stmt.setString(2, usuario.getEmail());
            stmt.setString(3, usuario.getPasswordHash());
            stmt.setString(4, usuario.getRol());
            stmt.executeUpdate();

            try (ResultSet generatedKeys = stmt.getGeneratedKeys()) {
                if (generatedKeys.next()) {
                    return generatedKeys.getInt(1);
                }
            }

            return 0;
        } catch (SQLException e) {
            throw new RuntimeException("Error al crear usuario", e);
        }
    }

    @Override
    public boolean existeEmail(String email) {
        String sql = "SELECT 1 FROM usuarios WHERE email = ? LIMIT 1";

        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, email);

            try (ResultSet rs = stmt.executeQuery()) {
                return rs.next();
            }
        } catch (SQLException e) {
            throw new RuntimeException("Error al verificar email", e);
        }
    }

    private Usuario mapearUsuario(ResultSet rs) throws SQLException {
        return new Usuario(
            rs.getInt("id"),
            rs.getString("nombre"),
            rs.getString("email"),
            rs.getString("password_hash"),
            rs.getString("rol")
        );
    }
}
