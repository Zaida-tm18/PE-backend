package com.uteq.veterinaria.service;

import com.uteq.veterinaria.model.Usuario;
import com.uteq.veterinaria.repository.UsuarioRepository;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;

import java.util.ArrayList;
import java.util.List;
import java.util.regex.Pattern;

/**
 * Logica de negocio del registro de usuarios. Equivalente Java del metodo
 * AuthController::registrar() de PHP.
 */
@Service
public class RegistroService {

    private static final Pattern EMAIL_PATTERN =
        Pattern.compile("^[\\w.+-]+@[\\w-]+\\.[a-zA-Z]{2,}$");

    private final UsuarioRepository usuarioRepository;
    private final PasswordEncoder passwordEncoder;

    public RegistroService(UsuarioRepository usuarioRepository, PasswordEncoder passwordEncoder) {
        this.usuarioRepository = usuarioRepository;
        this.passwordEncoder = passwordEncoder;
    }

    public static class ResultadoRegistro {
        public final boolean exito;
        public final List<String> errores;

        public ResultadoRegistro(boolean exito, List<String> errores) {
            this.exito = exito;
            this.errores = errores;
        }
    }

    public ResultadoRegistro registrar(String nombre, String email, String password, String passwordConfirm) {
        List<String> errores = new ArrayList<>();

        nombre = nombre == null ? "" : nombre.trim();
        email = email == null ? "" : email.trim();
        password = password == null ? "" : password;

        if (nombre.length() < 3) {
            errores.add("El nombre debe tener al menos 3 caracteres.");
        }

        if (!EMAIL_PATTERN.matcher(email).matches()) {
            errores.add("El correo electronico no es valido.");
        }

        boolean tieneLetra = password.chars().anyMatch(Character::isLetter);
        boolean tieneNumero = password.chars().anyMatch(Character::isDigit);

        if (password.length() < 8 || !tieneLetra || !tieneNumero) {
            errores.add("La contrasena debe tener minimo 8 caracteres, con letras y numeros.");
        }

        if (!password.equals(passwordConfirm)) {
            errores.add("Las contrasenas no coinciden.");
        }

        if (errores.isEmpty() && usuarioRepository.existeEmail(email)) {
            errores.add("Ese correo ya esta registrado.");
        }

        if (!errores.isEmpty()) {
            return new ResultadoRegistro(false, errores);
        }

        // BCryptPasswordEncoder: equivalente Java de password_hash() de PHP.
        // Spring Security lo trae integrado; en PHP hubo que llamar a la
        // funcion nativa directamente (un punto mas para la tabla comparativa).
        String hash = passwordEncoder.encode(password);

        Usuario usuario = new Usuario(null, nombre, email, hash, "recepcionista");
        usuarioRepository.crear(usuario);

        return new ResultadoRegistro(true, errores);
    }
}
