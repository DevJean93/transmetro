-- Reporte de Buses asignados a cada línea (incluye buses de reserva sin línea), con piloto y parqueo
SELECT
    COALESCE(l.nombre, 'Sin asignar')   AS linea,
    b.placa,
    b.modelo,
    b.capacidad_maxima,
    COALESCE(pi.nombre, '—')            AS piloto,
    p.nombre                            AS parqueo,
    ep.nombre                           AS estacion_parqueo
FROM buses b
LEFT JOIN lineas l       ON l.id = b.linea_id
LEFT JOIN pilotos pi     ON pi.bus_id = b.id
JOIN parqueos p          ON p.id = b.parqueo_id
JOIN estaciones ep       ON ep.id = p.estacion_id
ORDER BY l.id IS NULL, l.id, b.placa;
