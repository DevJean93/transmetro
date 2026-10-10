-- Reporte de Estaciones: líneas a las que pertenece, accesos, guardias, parqueos y ocupación actual
SELECT
    e.id,
    e.nombre                                        AS estacion,
    m.nombre                                        AS municipalidad,
    (SELECT GROUP_CONCAT(l.nombre ORDER BY l.nombre SEPARATOR ', ')
       FROM linea_estacion le JOIN lineas l ON l.id = le.linea_id
      WHERE le.estacion_id = e.id)                  AS lineas,
    (SELECT COUNT(*) FROM accesos a WHERE a.estacion_id = e.id)          AS accesos,
    (SELECT COUNT(*) FROM guardias g JOIN accesos a ON a.id = g.acceso_id
      WHERE a.estacion_id = e.id)                   AS guardias,
    (SELECT GROUP_CONCAT(p.nombre SEPARATOR ', ')
       FROM parqueos p WHERE p.estacion_id = e.id)  AS parqueos,
    e.capacidad,
    e.pasajeros_espera,
    ROUND(e.pasajeros_espera * 100 / e.capacidad)   AS ocupacion_pct
FROM estaciones e
JOIN municipalidades m ON m.id = e.municipalidad_id
ORDER BY e.nombre;
