package com.uteq.veterinaria.service;

import com.uteq.veterinaria.model.Usuario;
import com.uteq.veterinaria.repository.UsuarioRepository;
import org.springframework.security.core.userdetails.User;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.core.userdetails.UserDetailsService;
import org.springframework.security.core.userdetails.UsernameNotFoundException;
import org.springframework.stereotype.Service;

/**
 * Implementacion de UserDetailsService: le dice a Spring Security DONDE
 * buscar usuarios (nuestra tabla "usuarios" via UsuarioRepository) y CON QUE
 * hash compararlos. A partir de aqui, Spring Security se encarga solo de
 * todo lo demas: comparar password con password_verify() interno
 * (BCryptPasswordEncoder.matches, que tambien usa comparacion de tiempo
 * constante), regenerar el ID de sesion al autenticar (session fixation),
 * e invalidar la sesion en logout.
 *
 * Esto contrasta con PHP, donde TODO eso (login(), session_regenerate_id,
 * destruccion de sesion en logout) se escribio a mano en AuthController.
 */
@Service
public class UsuarioDetailsService implements UserDetailsService {

    private final UsuarioRepository usuarioRepository;

    public UsuarioDetailsService(UsuarioRepository usuarioRepository) {
        this.usuarioRepository = usuarioRepository;
    }

    @Override
    public UserDetails loadUserByUsername(String email) throws UsernameNotFoundException {
        Usuario usuario = usuarioRepository.buscarPorEmail(email)
            .orElseThrow(() -> new UsernameNotFoundException("Credenciales invalidas"));

        return User.builder()
            .username(usuario.getEmail())
            .password(usuario.getPasswordHash())
            .roles(usuario.getRol().toUpperCase())
            .build();
    }
}
