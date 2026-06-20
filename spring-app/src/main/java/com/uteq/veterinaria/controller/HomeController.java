package com.uteq.veterinaria.controller;

import org.springframework.security.core.Authentication;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

/**
 * Equivalente Java de public/index.php (el dashboard). No necesita llamar
 * a ningun "requireAuth()" manual: la regla .anyRequest().authenticated()
 * de SecurityConfig ya protege esta ruta. Si alguien entra sin sesion,
 * Spring Security lo redirige solo a /login.
 */
@Controller
public class HomeController {

    @GetMapping("/")
    public String dashboard(Authentication authentication, Model model) {
        model.addAttribute("email", authentication.getName());
        model.addAttribute("rol", authentication.getAuthorities().iterator().next().getAuthority());
        return "index";
    }
}
