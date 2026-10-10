-- Reporte de Accesos por línea (Req. 8) y verificación de al menos un guardia por acceso (Req. 10)
SELECT
    l.nombre                                            AS linea,
    le.orden,
    e.nombre                                            AS estacion,
    a.nombre                                            AS acceso,
    COUNT(g.id)                                         AS guardias,
    COALESCE(GROUP_CONCAT(g.nombre SEPARATOR ', '), '—') AS nombres_guardias,
    IF(COUNT(g.id) = 0, 'Sin guardia', 'Cumple')        AS estado
FROM lineas l
JOIN linea_estacion le ON le.linea_id = l.id
JOIN estaciones e      ON e.id = le.estacion_id
JOIN accesos a         ON a.estacion_id = e.id
LEFT JOIN guardias g   ON g.acceso_id = a.id
GROUP BY l.id, l.nombre, le.orden, e.nombre, a.id, a.nombre
ORDER BY l.id, le.orden, a.nombre;
