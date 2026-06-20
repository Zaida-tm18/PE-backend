package com.uteq.veterinaria.controller;

import com.uteq.veterinaria.model.Mascota;
import com.uteq.veterinaria.repository.UsuarioRepository;
import com.uteq.veterinaria.service.MascotaService;
import org.springframework.security.core.Authentication;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;

import java.time.LocalDate;
import java.time.format.DateTimeParseException;
import java.util.List;
import java.util.Optional;

/**
 * Equivalente Java de public/mascotas/*.php. Mismas 5 operaciones del
 * CRUD, mismas reglas (eliminar solo por POST con CSRF, que aqui valida
 * Spring Security automaticamente para CUALQUIER POST de la app).
 */
@Controller
@RequestMapping("/mascotas")
public class MascotaWebController {

    private final MascotaService mascotaService;
    private final UsuarioRepository usuarioRepository;

    public MascotaWebController(MascotaService mascotaService, UsuarioRepository usuarioRepository) {
        this.mascotaService = mascotaService;
        this.usuarioRepository = usuarioRepository;
    }

    @GetMapping
    public String listar(Model model) {
        List<Mascota> mascotas = mascotaService.listar();
        model.addAttribute("mascotas", mascotas);
        return "mascotas/index";
    }

    @GetMapping("/crear")
    public String mostrarFormularioCrear(Model model) {
        model.addAttribute("mascota", new Mascota());
        model.addAttribute("esEdicion", false);
        model.addAttribute("errores", new java.util.ArrayList<String>());
        return "mascotas/formulario";
    }

    @PostMapping("/crear")
    public String procesarCrear(
        @ModelAttribute MascotaForm form,
        Authentication authentication,
        Model model
    ) {
        Mascota mascota = mapearDesdeFormulario(form, model);
        if (mascota == null) {
            model.addAttribute("esEdicion", false);
            return "mascotas/formulario";
        }

        Integer usuarioId = usuarioRepository.buscarPorEmail(authentication.getName())
            .map(u -> u.getId())
            .orElse(null);
        mascota.setUsuarioId(usuarioId);

        MascotaService.ResultadoOperacion resultado = mascotaService.crear(mascota);

        if (!resultado.exito) {
            model.addAttribute("mascota", mascota);
            model.addAttribute("esEdicion", false);
            model.addAttribute("errores", resultado.errores);
            return "mascotas/formulario";
        }

        return "redirect:/mascotas?ok";
    }

    @GetMapping("/editar/{id}")
    public String mostrarFormularioEditar(@PathVariable int id, Model model) {
        Optional<Mascota> mascota = mascotaService.obtener(id);

        if (mascota.isEmpty()) {
            return "redirect:/mascotas";
        }

        model.addAttribute("mascota", mascota.get());
        model.addAttribute("esEdicion", true);
        model.addAttribute("errores", new java.util.ArrayList<String>());
        return "mascotas/formulario";
    }

    @PostMapping("/editar/{id}")
    public String procesarEditar(
        @PathVariable int id,
        @ModelAttribute MascotaForm form,
        Model model
    ) {
        Mascota mascota = mapearDesdeFormulario(form, model);
        if (mascota == null) {
            mascota = new Mascota();
            mascota.setId(id);
            model.addAttribute("mascota", mascota);
            model.addAttribute("esEdicion", true);
            return "mascotas/formulario";
        }

        MascotaService.ResultadoOperacion resultado = mascotaService.actualizar(id, mascota);

        if (!resultado.exito) {
            mascota.setId(id);
            model.addAttribute("mascota", mascota);
            model.addAttribute("esEdicion", true);
            model.addAttribute("errores", resultado.errores);
            return "mascotas/formulario";
        }

        return "redirect:/mascotas?ok";
    }

    @PostMapping("/eliminar/{id}")
    public String eliminar(@PathVariable int id) {
        // El token CSRF de este formulario lo valida Spring Security
        // automaticamente ANTES de que esta linea siquiera se ejecute.
        mascotaService.eliminar(id);
        return "redirect:/mascotas?ok";
    }

    /**
     * Convierte el formulario plano (strings) en un objeto Mascota
     * tipado, parseando la fecha a mano. Si la fecha viene mal formada,
     * agrega el error al modelo y devuelve null para que el controlador
     * vuelva a mostrar el formulario.
     */
    private Mascota mapearDesdeFormulario(MascotaForm form, Model model) {
        Mascota mascota = new Mascota();
        mascota.setNombre(form.getNombre());
        mascota.setEspecie(form.getEspecie());
        mascota.setRaza(form.getRaza());
        mascota.setPropietarioNombre(form.getPropietarioNombre());
        mascota.setPropietarioTelefono(form.getPropietarioTelefono());

        if (form.getFechaNacimiento() != null && !form.getFechaNacimiento().isBlank()) {
            try {
                mascota.setFechaNacimiento(LocalDate.parse(form.getFechaNacimiento()));
            } catch (DateTimeParseException e) {
                model.addAttribute("errores", List.of("La fecha de nacimiento no tiene un formato valido."));
                model.addAttribute("mascota", mascota);
                return null;
            }
        }

        return mascota;
    }

    /** DTO plano que recibe los campos del formulario HTML tal cual llegan (todos String). */
    public static class MascotaForm {
        private String nombre;
        private String especie;
        private String raza;
        private String fechaNacimiento;
        private String propietarioNombre;
        private String propietarioTelefono;

        public String getNombre() {
            return nombre;
        }

        public void setNombre(String nombre) {
            this.nombre = nombre;
        }

        public String getEspecie() {
            return especie;
        }

        public void setEspecie(String especie) {
            this.especie = especie;
        }

        public String getRaza() {
            return raza;
        }

        public void setRaza(String raza) {
            this.raza = raza;
        }

        public String getFechaNacimiento() {
            return fechaNacimiento;
        }

        public void setFechaNacimiento(String fechaNacimiento) {
            this.fechaNacimiento = fechaNacimiento;
        }

        public String getPropietarioNombre() {
            return propietarioNombre;
        }

        public void setPropietarioNombre(String propietarioNombre) {
            this.propietarioNombre = propietarioNombre;
        }

        public String getPropietarioTelefono() {
            return propietarioTelefono;
        }

        public void setPropietarioTelefono(String propietarioTelefono) {
            this.propietarioTelefono = propietarioTelefono;
        }
    }
}