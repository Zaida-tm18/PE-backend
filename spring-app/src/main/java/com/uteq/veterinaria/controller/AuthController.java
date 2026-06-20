package com.uteq.veterinaria.controller;

import com.uteq.veterinaria.service.RegistroService;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;

/**
 * Controlador de autenticacion.
 *
 * OJO: aqui NO hay un metodo para procesar el POST de /login. Spring
 * Security intercepta esa ruta automaticamente (ver SecurityConfig,
 * loginProcessingUrl("/login")) y hace todo el trabajo: comparar
 * password con BCryptPasswordEncoder, regenerar sesion, etc.
 * En PHP, ese mismo trabajo lo hacia AuthController::login() a mano.
 */
@Controller
public class AuthController {

    private final RegistroService registroService;

    public AuthController(RegistroService registroService) {
        this.registroService = registroService;
    }

    @GetMapping("/login")
    public String mostrarLogin(
        @RequestParam(value = "error", required = false) String error,
        @RequestParam(value = "logout", required = false) String logout,
        Model model
    ) {
        if (error != null) {
            model.addAttribute("errorLogin", "Credenciales invalidas.");
        }
        if (logout != null) {
            model.addAttribute("mensajeLogout", "Sesion cerrada correctamente.");
        }
        return "auth/login";
    }

    @GetMapping("/register")
    public String mostrarRegistro(Model model) {
        model.addAttribute("errores", new java.util.ArrayList<String>());
        return "auth/register";
    }

    @PostMapping("/register")
    public String procesarRegistro(
        @RequestParam String nombre,
        @RequestParam String email,
        @RequestParam String password,
        @RequestParam("password_confirm") String passwordConfirm,
        Model model
    ) {
        RegistroService.ResultadoRegistro resultado =
            registroService.registrar(nombre, email, password, passwordConfirm);

        if (resultado.exito) {
            return "redirect:/login?registrado";
        }

        model.addAttribute("errores", resultado.errores);
        return "auth/register";
    }
}
