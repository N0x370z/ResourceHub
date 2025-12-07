--Ejecutar el siguiente script en la consola de myqsl para actualizar la tabla de descargas
UPDATE bitacora_descargas 
SET 
    dia_semana = ELT(DAYOFWEEK(fecha_hora), 'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'),
    hora_descarga = TIME(fecha_hora)
WHERE dia_semana IS NULL OR hora_descarga IS NULL;