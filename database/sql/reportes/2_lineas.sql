-- Reporte de Líneas: recorrido ordenado, distancia total y cumplimiento de la regla de buses (n <= buses <= 2n)
SELECT
    l.id,
    l.nombre                                                    AS linea,
    m.nombre                                                    AS municipalidad,
    COUNT(le.estacion_id)                                       AS estaciones,
    GROUP_CONCAT(e.nombre ORDER BY le.orden SEPARATOR ' → ')    AS recorrido,
    COALESCE(SUM(le.distancia_siguiente_km), 0)                 AS distancia_km,
    (SELECT COUNT(*) FROM buses b WHERE b.linea_id = l.id)      AS buses_asignados,
    COUNT(le.estacion_id)                                       AS buses_minimo,
    COUNT(le.estacion_id) * 2                                   AS buses_maximo,
    CASE
        WHEN (SELECT COUNT(*) FROM buses b WHERE b.linea_id = l.id) < COUNT(le.estacion_id) THEN 'Faltan buses'
        WHEN (SELECT COUNT(*) FROM buses b WHERE b.linea_id = l.id) > COUNT(le.estacion_id) * 2 THEN 'Exceso de buses'
        ELSE 'Cumple'
    END                                                         AS estado
FROM lineas l
JOIN municipalidades m ON m.id = l.municipalidad_id
LEFT JOIN linea_estacion le ON le.linea_id = l.id
LEFT JOIN estaciones e ON e.id = le.estacion_id
GROUP BY l.id, l.nombre, m.nombre
ORDER BY l.id;
