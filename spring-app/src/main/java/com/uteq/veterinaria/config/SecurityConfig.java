package com.uteq.veterinaria.config;

import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.security.config.annotation.web.builders.HttpSecurity;
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.security.web.SecurityFilterChain;
import org.springframework.security.web.header.writers.ReferrerPolicyHeaderWriter;

/**
 * Configuracion central de seguridad (OE4).
 *
 * IMPORTANTE para tu informe (Anexo C, pregunta de Analisis): varias
 * protecciones que en PHP se implementaron a mano vienen ACTIVADAS POR
 * DEFECTO aqui, sin escribir una sola linea para activarlas:
 *   - Proteccion CSRF en todo POST/PUT/DELETE (Spring Security la activa
 *     sola; el desarrollador solo debe acordarse de incluir el token en
 *     los formularios, ver los .html en templates/).
 *   - Regeneracion del ID de sesion al autenticarse (session fixation).
 *   - Cabeceras X-Content-Type-Options, X-Frame-Options y Cache-Control
 *     en cada respuesta.
 * Nosotros solo agregamos explicitamente la Content-Security-Policy y la
 * Referrer-Policy, que Spring Security no asume por defecto porque dependen
 * mucho de cada aplicacion.
 */
@Configuration
@EnableWebSecurity
public class SecurityConfig {

    @Bean
    public PasswordEncoder passwordEncoder() {
        // BCrypt: equivalente Java de PASSWORD_BCRYPT en PHP. Spring Security
        // tambien soporta Argon2PasswordEncoder si se quisiera igualar
        // exactamente el PASSWORD_ARGON2ID usado en la version PHP.
        return new BCryptPasswordEncoder();
    }

    @Bean
    public SecurityFilterChain filterChain(HttpSecurity http) throws Exception {
        http
            .authorizeHttpRequests(auth -> auth
                .requestMatchers("/register", "/css/**", "/js/**").permitAll()
                .anyRequest().authenticated()
            )
            .formLogin(form -> form
                .loginPage("/login")
                .loginProcessingUrl("/login")
                .defaultSuccessUrl("/", true)
                .failureUrl("/login?error")
                .permitAll()
            )
            .logout(logout -> logout
                .logoutUrl("/logout")
                .logoutSuccessUrl("/login?logout")
                .invalidateHttpSession(true)
                .deleteCookies("JSESSIONID")
                .permitAll()
            )
            // CSRF: NO se desactiva. Queda con la configuracion por
            // defecto de Spring Security (activa para toda peticion que
            // modifique estado). Cada formulario en /templates incluye el
            // campo oculto _csrf como vimos en PHP, pero aqui el TOKEN en
            // si lo genera y valida el framework, no nosotros.
            .headers(headers -> headers
                .contentSecurityPolicy(csp -> csp.policyDirectives(
                    "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'"))
                .referrerPolicy(referrer -> referrer.policy(
                    ReferrerPolicyHeaderWriter.ReferrerPolicy.STRICT_ORIGIN_WHEN_CROSS_ORIGIN))
            );

        return http.build();
    }
}
