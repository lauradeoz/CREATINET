<?php
/**
 * data/ususarioDB.php
 *
 * Esta clase `UsuarioDB` se encarga de todas las operaciones de la base de datos
 * relacionadas con los usuarios. Incluye métodos para la gestión de usuarios
 * como obtener, crear, actualizar, eliminar, verificar credenciales,
 * manejar tokens de verificación y recuperación de contraseña, etc.
 */

// Incluye el archivo de configuración global (donde se define URL_ADMIN).
 require_once __DIR__ . '/../config/config.php';
// Incluye la clase para el envío de correos electrónicos.
 require_once __DIR__ . '/enviarCorreos.php';

class UsuarioDB {

    private $db;    // Propiedad para almacenar la conexión a la base de datos.
    private $table = 'usuarios'; // Nombre de la tabla de usuarios en la base de datos.
    private $url = URL_ADMIN; // URL base de la aplicación, usada para enlaces en correos.
    
    /**
     * Constructor de la clase UsuarioDB.
     *
     * @param Database $database Una instancia de la clase Database que proporciona la conexión.
     */
    public function __construct($database){
        // Obtiene la conexión mysqli del objeto Database.
        $this->db = $database->getConexion();
    }

    /**
     * Obtiene todos los usuarios de la base de datos.
     *
     * @return array Un array de arrays asociativos, donde cada uno representa un usuario.
     *               Retorna un array vacío si no hay usuarios o si la consulta falla.
     */
    public function getAll(){
        $sql = "SELECT * FROM {$this->table}";
        $resultado = $this->db->query($sql);

        if($resultado && $resultado->num_rows > 0){
            $usuarios = [];
            while($row = $resultado->fetch_assoc()){
                $usuarios[] = $row;
            }
            return $usuarios;
        }
        return [];
    }

    /**
     * Obtiene un usuario por su ID.
     *
     * @param int $id ID del usuario a buscar.
     * @return array|null Un array asociativo con los datos del usuario, o null si no se encuentra.
     */
    public function getById($id){
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            $stmt->bind_param("i", $id); // Vincula el ID como parámetro entero.
            $stmt->execute(); // Ejecuta la consulta.
            $result = $stmt->get_result(); // Obtiene el resultado.

            if($result->num_rows > 0){
                $usuario = $result->fetch_assoc(); // Obtiene la fila como array asociativo.
                $stmt->close(); // Cierra la declaración.
                return $usuario;
            }
            $stmt->close(); // Cierra la declaración si no se encuentra el usuario.
        }
        return null; // Retorna null si la preparación de la consulta falla o no se encuentra el usuario.
    }

    /**
     * Busca un usuario por su correo electrónico.
     *
     * @param string $correo Correo electrónico del usuario a buscar.
     * @return array|null Un array asociativo con los datos del usuario, o null si no se encuentra.
     */
    public function getByEmail($correo){
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            $stmt->bind_param("s", $correo); // Vincula el correo como parámetro de cadena.
            $stmt->execute(); // Ejecuta la consulta.
            $result = $stmt->get_result(); // Obtiene el resultado.

            if($result->num_rows > 0){
                $usuario = $result->fetch_assoc(); // Obtiene la fila como array asociativo.
                $stmt->close(); // Cierra la declaración.
                return $usuario;
            }
            $stmt->close(); // Cierra la declaración si no se encuentra el usuario.
        }
        return null; // Retorna null si la preparación de la consulta falla o no se encuentra el usuario.
    }

    /**
     * Registra un nuevo usuario en la base de datos.
     *
     * @param string $nombre   Nombre del usuario.
     * @param string $email    Correo electrónico del usuario (debe ser único).
     * @param string $password Contraseña del usuario (se hasheará antes de guardar).
     * @param int    $verificado Estado de verificación (0 por defecto para no verificado).
     * @return array Un array asociativo con el estado de éxito y un mensaje.
     */
    public function registrarUsuario($nombre, $email, $password, $verificado = 0){
        // Hashea la contraseña para seguridad.
        $password = password_hash($password, PASSWORD_DEFAULT);
        // Genera un token único para la verificación de la cuenta.
        $token = $this->generarToken();

        // Comprueba si el correo electrónico ya existe en la base de datos.
        $existe = $this->correoExiste($email);

        // Asigna un valor por defecto para token_recuperacion (NULL en la DB).
        $token_recuperacion_default = ''; 

        // Prepara la consulta SQL para insertar un nuevo usuario.
        $sql = "INSERT INTO usuarios (nombre, email, password, token, token_recuperacion, verificado) VALUES(?,?,?,?,?,?)";
        $stmt = $this->db->prepare($sql);
        // Vincula los parámetros a la consulta preparada.
        $stmt->bind_param("sssssi", $nombre, $email, $password, $token, $token_recuperacion_default, $verificado);

        if(!$existe){ // Si el correo no existe, procede con el registro.
            if($stmt->execute()){ // Ejecuta la consulta.
                // Si la inserción es correcta, envía un correo de verificación.
                $mensaje_email = "Por favor, verifica tu cuenta haciendo clic en este enlace: " . URL_ADMIN . "verificar.php?token=$token";
                $correoEnviado = Correo::enviarCorreo($email, "Cliente", "Verificación de cuenta", $mensaje_email);
                
                if ($correoEnviado['success']) {
                    $resultado = ["success" => true, "mensaje" => "Registro exitoso. Por favor, verifica tu correo."];
                } else {
                    $resultado = ["success" => false, "mensaje" => "Registro exitoso, pero hubo un error al enviar el correo de verificación: " . $correoEnviado['mensaje']];
                }
            }else{
                // Si la ejecución de la consulta falla.
                $resultado = ["success" => false, "mensaje" => "Error en el registro: " . $stmt->error];
            }
        }else{
            // Si el correo ya existe.
            $resultado = ["success" => false, "mensaje" => "Ya existe una cuenta con ese email"];
        }
        $stmt->close(); // Cierra la declaración.
        return $resultado;
    }

    /**
     * Actualiza los datos de un usuario existente.
     *
     * @param int   $id   ID del usuario a actualizar.
     * @param array $data Array asociativo con los datos a actualizar (email, nombre, password opcional).
     * @return array|bool Un array asociativo con los datos actualizados del usuario, o false si falla.
     */
    public function update($id, $data){
        $sql = "UPDATE {$this->table} SET email = ?, nombre = ?";
        $params = [$data['correo'], $data['nombre']];
        $types = "ss"; // Corregido: 'ss' para email y nombre.

        // Si se proporciona una nueva contraseña, la incluye en la actualización.
        if(isset($data['password']) && !empty($data['password'])){
            $sql .= ", password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            $types .= "s";
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;
        $types .= "i"; // 'i' para el ID.

        $stmt = $this->db->prepare($sql);
        if($stmt){
            // Usa el operador splat (...) para desempaquetar el array de parámetros.
            $stmt->bind_param($types, ...$params);
            
            if($stmt->execute()){
                $stmt->close();
                return $this->getById($id); // Retorna el usuario actualizado.
            }
            $stmt->close();
        }
        return false; // Retorna false si la preparación o ejecución falla.
    }

    /**
     * Elimina un usuario de la base de datos.
     *
     * @param int $id ID del usuario a eliminar.
     * @return bool True si la eliminación fue exitosa, false en caso contrario.
     */
    public function delete($id){
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            $stmt->bind_param("i", $id); // Vincula el ID como parámetro entero.
            $result = $stmt->execute(); // Ejecuta la consulta.
            $stmt->close(); // Cierra la declaración.
            return $result; // Retorna true/false según el éxito de la ejecución.
        }
        return false; // Retorna false si la preparación falla.
    }

    /**
     * Verifica las credenciales de inicio de sesión de un usuario.
     *
     * @param string $correo   Correo electrónico del usuario.
     * @param string $password Contraseña proporcionada por el usuario.
     * @return array Un array asociativo con el estado de éxito, un mensaje y los datos del usuario (si es exitoso).
     */
    public function verificarCredenciales($correo, $password){
        $usuario = $this->getByEmail($correo); // Busca el usuario por correo.

        // Si no existe el usuario.
        if(!$usuario){
            return ['success' => false, 'mensaje' => 'Usuario no encontrado'];
        }

        // Verificar si el usuario está bloqueado.
        if($usuario['bloqueado'] == 1){
            return ['success' => false, 'mensaje' => 'Usuario bloqueado'];
        }

        // Comprobar que el usuario ha verificado el correo.
        if($usuario['verificado'] === 0){
            return['success' => false, 'mensaje' => 'Verifica tu correo'];
        }

        // Verificar la contraseña hasheada.
        if(!password_verify($password, $usuario['password'])){
            return ['success' => false, 'mensaje' => 'Contraseña incorrecta'];
        }

        // Credenciales correctas - actualizar la fecha de último acceso.
        $this->actualizarUltimoAcceso($usuario['id']);
        
        // No devolver la contraseña ni los tokens por seguridad.
        unset($usuario['password']);
        unset($usuario['token']);
        unset($usuario['token_recuperacion']);
        
        return ['success' => true, 'usuario' => $usuario, 'mensaje' => 'Login correcto'];        
    }

    /**
     * Actualiza la fecha y hora del último acceso de un usuario.
     *
     * @param int $id ID del usuario.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizarUltimoAcceso($id){
        $sql = "UPDATE {$this->table} SET ultima_conexion = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            $stmt->bind_param("i", $id); // Vincula el ID como parámetro entero.
            $result = $stmt->execute(); // Ejecuta la consulta.
            $stmt->close(); // Cierra la declaración.
            return $result; // Retorna true/false según el éxito de la ejecución.
        }
        return false; // Retorna false si la preparación falla.
    }

    /**
     * Cambia el estado de bloqueo de un usuario.
     *
     * @param int $id        ID del usuario.
     * @param int $bloqueado Nuevo estado de bloqueo (1 para bloqueado, 0 para desbloqueado).
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function cambiarEstadoBloqueado($id, $bloqueado = 1){
        $sql = "UPDATE {$this->table} SET bloqueado = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            $stmt->bind_param("ii", $bloqueado, $id); // Vincula los parámetros.
            $result = $stmt->execute(); // Ejecuta la consulta.
            $stmt->close(); // Cierra la declaración.
            return $result; // Retorna true/false según el éxito de la ejecución.
        }
        return false; // Retorna false si la preparación falla.
    }


    public function recuperarPassword($email){

        $existe = $this->correoExiste($email);

        $resultado = ["success" => false, "mensaje" => "El correo electrónico proporcionado no corresponde a ningún usuario registrado."];

        //si el correo existe en la bbdd
        if($existe){
            $token = $this->generarToken(); // Genera un token de recuperación.

            // Actualiza el token de recuperación en la base de datos para el usuario.
            $sql = "UPDATE usuarios SET token_recuperacion = ? WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ss", $token, $email);

            // Ejecuta la consulta.
            if($stmt->execute()){
                // Prepara el mensaje del correo electrónico con el enlace de restablecimiento.
                $mensaje = "Para restablecer tu contraseña, haz click en este enlace: " . $this->url . "restablecer.php?token=" . $token;
                // Envía el correo electrónico.
                $correoEnviado = Correo::enviarCorreo($email, "Cliente", "Restablecer Contraseña", $mensaje);
                
                if ($correoEnviado['success']) {
                    $resultado = ["success" => true, "mensaje" => "Se ha enviado un enlace de recuperación a tu correo"];
                } else {
                    $resultado = ["success" => false, "mensaje" => "Error al enviar el correo de recuperación: " . $correoEnviado['mensaje']];
                }
            }else{
                $resultado = ["success" => false, "mensaje" => "Error al procesar la solicitud de recuperación"];
            }
            $stmt->close(); // Cierra la declaración.
        }
        return $resultado;
    }

    /**
     * Restablece la contraseña de un usuario utilizando un token de recuperación.
     *
     * @param string $token          Token de recuperación.
     * @param string $nueva_password Nueva contraseña (se hasheará).
     * @return array Un array asociativo con el estado de éxito y un mensaje.
     */
public function restablecerPassword($token, $nueva_password){
        // Hashea la nueva contraseña.
        $password = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        // Busca al usuario con el token proporcionado.
        $sql = "SELECT id FROM usuarios WHERE token_recuperacion = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        $resultado = ["success" => false, "mensaje" => "El token de recuperación no es válido o ya ha sido utilizado"];

        if($result->num_rows === 1){
            $row = $result->fetch_assoc();
            $user_id = $row['id'];

            // Actualiza la contraseña y elimina el token de recuperación.
            $update_sql = "UPDATE usuarios SET password = ?, token_recuperacion = NULL WHERE id = ?";
            $update_stmt = $this->db->prepare($update_sql);
            $update_stmt->bind_param("si", $password, $user_id);

            if($update_stmt->execute()){
                $resultado = ["success" => true, "mensaje" => "Tu contraseña ha sido actualizada correctamente"];
            }else{
                $resultado = ["success" => false, "mensaje" => "Hubo un error al actualizar tu contraseña. Por favor, intenta de nuevo más tarde"];
            }
            $update_stmt->close(); // Cierra la declaración de actualización.
        }
        $stmt->close(); // Cierra la declaración de selección.
        return $resultado;
    }



    /**
     * Verifica un token de registro para activar la cuenta de un usuario.
     *
     * @param string $token Token de verificación.
     * @return array Un array asociativo con el estado de éxito y un mensaje.
     */
    public function verificarToken($token){
        // Busca al usuario con el token recibido y que no esté verificado.
        $sql = "SELECT id FROM usuarios WHERE token = ? AND verificado = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 1){
            //token es válido actualizamos el estado de verificación del usuario
            $row = $result->fetch_assoc();
            $user_id = $row['id'];
            
            $update_sql = "UPDATE usuarios SET verificado = 1, token = NULL WHERE id= ?";
            $update_stmt = $this->db->prepare($update_sql);
            $update_stmt->bind_param("i", $user_id);

            $resultado = ["success" => false, "mensaje" => "Hubo un error al verificar tu cuenta. Por favor, intenta de nuevo más tarde"];

            if($update_stmt->execute()){
                $resultado = ["success" => true, "mensaje" => "Tu cuenta ha sido verificada. Ahora puedes iniciar sesión"];
            }
            $update_stmt->close(); // Cierra la declaración de actualización.
        }
        else{
            // No hay usuario con ese token o ya está verificado.
            $resultado = ["success" => false, "mensaje" => "Token no válido"];
        }
        $stmt->close(); // Cierra la declaración de selección.
        return $resultado;
    }    



    /**
     * Verificar si un correo ya existe
     *
     * @param string $correo    Correo electrónico a verificar.
     * @param int|null $excludeId ID de usuario a excluir de la búsqueda (útil para actualizaciones).
     * @return bool True si el correo existe, false en caso contrario.
     */
    public function correoExiste($correo, $excludeId = null){
        $sql = "SELECT id FROM {$this->table} WHERE email = ?";
        $params = [$correo];
        $types = "s";

        if($excludeId){
            $sql .= " AND id != ?";
            $params[] = $excludeId;
            $types .= "i";
        }

        $stmt = $this->db->prepare($sql);
        if($stmt){
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->num_rows > 0;
            $stmt->close();
            return $exists;
        }
        return false; // Retorna false si la preparación falla.
    }

    /**
     * Función para simular el envío de correo electrónico y registrarlo en un log.
     * (Útil para desarrollo sin necesidad de un servidor SMTP real).
     *
     * @param string $destinatario Correo electrónico del destinatario.
     * @param string $asunto       Asunto del correo.
     * @param string $mensaje      Contenido del mensaje.
     * @return array Un array asociativo con el estado de éxito y un mensaje.
     */
    public function enviarCorreoSimulado($destinatario, $asunto, $mensaje){
        $archivo_log = __DIR__ . '/correos_simulados.log'; // Ruta del archivo de log.
        $contenido = "Fecha: " . date('Y-m-d H:i:s') . "\n"; // Fecha y hora.
        $contenido .= "Para: $destinatario\n"; // Destinatario.
        $contenido .= "Asunto: $asunto\n"; // Asunto.
        $contenido .= "Mensaje:\n$mensaje\n"; // Mensaje.
        $contenido .= "__________________________________________\n\n"; // Separador.

        // Escribe el contenido en el archivo de log, añadiéndolo al final.
        file_put_contents($archivo_log, $contenido, FILE_APPEND);

        return ["success" => true, "mensaje" => "Registro exitoso. Por favor, verifica tu correo"];
    }

    /**
     * Genera un token aleatorio seguro.
     *
     * @return string Un token hexadecimal de 64 caracteres.
     */
    public function generarToken(){
        return bin2hex(random_bytes(32)); // Genera 32 bytes aleatorios y los convierte a hexadecimal.
    }
}