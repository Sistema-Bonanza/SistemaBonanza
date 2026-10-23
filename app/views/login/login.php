<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - Industrias Bonanza</title>
    
    <!-- BULMA CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Typography Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
        <link rel="stylesheet" href="assets/css/login.css" />
</head>
<body class="is-flex is-align-items-center is-justify-content-center p-4">

<main class="login-wrapper">
    <div class="columns is-gapless is-marginless">
        
        <!-- Panel Izquierdo -->
        <section class="column is-5 bg-gradient-blue">
            <div class="blueprint-grid"></div>
            
            <!-- Contenedor interno para recuperar el padding (p-6) -->
            <div class="is-flex is-flex-direction-column is-justify-content-space-between p-6" style="height: 100%; min-height: 600px; position: relative; z-index: 10;">
                
                <div>
                    <div class="is-flex is-align-items-center mb-6">
                        <div class="is-flex is-align-items-center is-justify-content-center mr-3" style="width: 48px; height: 48px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); border-radius: 0.75rem;">
                            <i class="fa-solid fa-industry is-size-5 has-text-white"></i>
                        </div>
                        <div>
                            <span class="is-block is-size-7 has-text-weight-bold is-uppercase mb-1" style="letter-spacing: 2px; color: #bfdbfe;">Plataforma Logística</span>
                            <span class="is-size-5 has-text-weight-bold font-display has-text-white">Industrias Bonanza</span>
                        </div>
                    </div>

                    <div class="is-inline-flex is-align-items-center px-3 py-1 mb-5" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 9999px;">
                        <span class="is-size-7 has-text-weight-medium" style="color: #dbeafe;">🟢 Sistema Operativo en Línea</span>
                    </div>

                    <h1 class="title is-3 has-text-white font-display mb-4" style="line-height: 1.3;">
                        Control de Almacén & Gestión de Inventarios
                    </h1>
                    <p class="is-size-6" style="color: rgba(219, 234, 254, 0.9); line-height: 1.6;">
                        Monitoreo integral de existencias, auditoría de despachos y administración de bodegas industriales con trazabilidad en tiempo real.
                    </p>
                </div>

                <div class="is-flex is-align-items-center is-justify-content-space-between pt-4 mt-6" style="border-top: 1px solid rgba(255,255,255,0.15);">
                    <div class="is-flex is-align-items-center is-size-7" style="font-family: monospace; color: #bfdbfe;">
                        <i class="fa-solid fa-terminal mr-2"></i>
                        <span>v2.4.0 Build Corporativo</span>
                    </div>
                    <span class="px-2 py-1 has-text-weight-bold is-size-7 rounded" style="font-family: monospace; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #dbeafe;">
                        Nodo #01
                    </span>
                </div>
            </div>
        </section>

        <!-- Panel Derecho -->
        <section class="column is-7 has-background-white">
            
            <!-- Contenedor interno para recuperar el padding (p-6) -->
            <div class="is-flex is-flex-direction-column is-justify-content-space-between p-6" style="height: 100%; min-height: 600px;">
                
                <div>
                    <div class="is-inline-flex is-align-items-center px-3 py-1 mb-5 has-text-weight-bold is-size-7 is-uppercase" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 9999px; letter-spacing: 1px;">
                        <i class="fa-solid fa-shield-halved mr-2"></i>
                        <span>Acceso a Terminal</span>
                    </div>

                    <h2 class="title is-3 font-display has-text-dark mb-3">Bienvenido de nuevo</h2>
                    <p class="subtitle is-6 has-text-grey mb-6" style="margin-top: 0 !important;">Ingrese sus credenciales operativas para iniciar turno.</p>

                    <?php if (isset($_GET['error'])): ?>
                    <div class="notification is-danger is-light mb-5 is-flex is-align-items-center p-3" style="border-radius: 0.75rem;">
                        <i class="fa-solid fa-circle-exclamation is-size-5 mr-3"></i>
                        <span class="has-text-weight-medium is-size-6"><?php echo htmlspecialchars($_GET['error']); ?></span>
                    </div>
                    <?php endif; ?>


                        <?= mostrarFlash() ?>
                    <form action="index.php?controller=sistema&action=procesarLogin" method="POST" id="loginForm" onsubmit="return handleLogin();">
                        <input type="hidden" name="login_form" value="1">

                        <div class="field mb-4">
                            <label class="label has-text-dark is-size-7 has-text-weight-bold">Usuario o Correo <span class="has-text-danger">*</span></label>
                            <div class="control has-icons-left">
                                <input class="input is-medium" style="border-radius: 0.75rem; box-shadow: none; border-color: #cbd5e1;" type="text" id="username" name="username" placeholder="Ej. usuario123" required autocomplete="username">
                                <span class="icon is-left has-text-grey-light">
                                    <i class="fa-solid fa-user"></i>
                                </span>
                            </div>
                        </div>

                        <div class="field mb-5">
                            <label class="label has-text-dark is-size-7 has-text-weight-bold">Contraseña <span class="has-text-danger">*</span></label>
                            <div class="control has-icons-left has-icons-right">
                                <input class="input is-medium" style="border-radius: 0.75rem; box-shadow: none; border-color: #cbd5e1;" type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                                <span class="icon is-left has-text-grey-light">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <span class="icon is-right" style="pointer-events: auto; cursor: pointer;" onclick="togglePasswordVisibility()">
                                    <i class="fa-solid fa-eye" id="password-toggle-icon"></i>
                                </span>
                            </div>
                        </div>

                        <div class="field mt-6">
                            <div class="control">
                                <button type="submit" id="submit-btn" class="button is-link is-medium is-fullwidth has-text-weight-bold" style="background-color: #2563eb; border-radius: 0.75rem; transition: all 0.3s ease;">
                                    <span id="btn-text">Ingresar al Sistema</span>
                                    <span class="icon is-small ml-2">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <footer class="is-flex is-align-items-center is-justify-content-space-between is-flex-wrap-wrap pt-4 mt-6" style="border-top: 1px solid #e2e8f0;">
                     <p class="is-size-7 has-text-grey m-0">
                            © <?php echo date('Y'); ?> <strong class="has-text-dark">Industrias Bonanza</strong> 
                        </p>
                        <div class="is-flex is-align-items-center is-size-7" style="gap: 12px;">
                            <a href="javascript:void(0)" class="has-text-grey hover-link" style="text-decoration: none;">Soporte Técnico</a>
                            <span class="has-text-grey-light">•</span>
                            <a href="javascript:void(0)" class="has-text-grey hover-link" style="text-decoration: none;">Normativa Almacén</a>
                        </div>
                    </footer>
                </div>
            </section>
        </div>
    </main>

     <!-- CDN de SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Puente seguro: Pasamos el error de PHP a una variable global de JS
        const errorMessage = "<?= isset($errorLogin) && !empty($errorLogin) ? htmlspecialchars($errorLogin) : '' ?>";
    </script>

    <!-- Archivo JavaScript externo y desacoplado -->
    <script src="assets/js/login.js"></script>
</body>
</html>