"""
# 1. Libraries & Extras
"""
import sys
import numpy as np
import pandas as pd
from scipy.spatial import distance_matrix
#import networkx as nx
from datetime import datetime
import holidays
from datetime import timedelta
import random
import time
import operator
import mysql.connector
import json

dias_de_la_semana = {
    0: "Lunes",
    1: "Martes",
    2: "Miercoles",
    3: "Jueves",
    4: "Viernes",
    5: "Sabado",
    6: "Domingo"
}

"""# 2. Funciones a Utilizar"""

def es_dia_laboral(dia):
  if type(dia) == str:
    dia = datetime.strptime(dia, '%d-%m-%Y').date()
  if dia.weekday() == 6:
    return False
  elif dia in holidays.MX(years=dia.year):
    return False
  else:
    return True

def list_to_date(idx,start):
  if type(start) == str:
    start = datetime.strptime(start, '%d-%m-%Y').date()
  return start + timedelta(days=idx)

def create_day_list(start, days):
  day_list = [0]*days
  for idx, i in enumerate(day_list):
    if es_dia_laboral(list_to_date(idx,start)):
      day_list[idx] = 1
  return day_list

def create_day_dict(start, daylist):
  day_dict = {
      list_to_date(i, start): {
          "info": {
              "day_name": "",
              "day_status": "",
              "day_id": "",
          },
          "order": [],
          "order_arrival": [],
          "order_inventory": [],
          "plantable": []
      }
      for i in range(len(daylist))
  }
  return day_dict

def label_dates(daydict):
  for idx, day in enumerate(daydict):
    daydict[day]["info"]["day_name"] = dias_de_la_semana[day.weekday()]
    if day.weekday() == 6:
      daydict[day]["info"]["day_status"] = "Fin de Semana"
    elif day in holidays.MX(years=day.year):
      daydict[day]["info"]["day_status"] = "Feriado"
    else:
      daydict[day]["info"]["day_status"] = "Dia Laboral"
    daydict[day]["info"]["day_id"] = idx
  return daydict

def create_dates(start, days):
  day_list = create_day_list(start, days)
  day_dict = create_day_dict(start, day_list)
  labled_day_dict = label_dates(day_dict)
  return labled_day_dict

def create_dist_df(centroides):
  names = list(centroides.keys())
  points = np.array([centroides[name] for name in names])
  dist_matrix = distance_matrix(points, points)
  return pd.DataFrame(dist_matrix, index=names, columns=names)

def agregar_pedidio_a_dict(day_dict, inicio, i, pedido_num, plantable_target_id, denvio, derposo, dplantable):
  pedido_num += 1
  day_dict[list_to_date(plantable_target_id - (dreposo + denvio + i), inicio)]["order"].append(pedido_num)
  day_dict[list_to_date(plantable_target_id - (dreposo + i), inicio)]["order_arrival"].append(pedido_num)
  for j in range(dreposo):
    day_dict[list_to_date(plantable_target_id - (dreposo + i - j), inicio)]["order_inventory"].append(pedido_num)
  for k in range(dplantable):
    day_dict[list_to_date(plantable_target_id + (k - i), inicio)]["plantable"].append(pedido_num)
  return day_dict, pedido_num

def perpendicular_distance(line, point):
    if line == base or line == point:
      return 99999
    line_point = dict_centroide_poligonos[line]
    target_point = dict_centroide_poligonos[point]

    x1, y1 = line_point
    x0, y0 = target_point

    numerator = abs(x1 * y0 - y1 * x0)
    denominator = np.sqrt(x1**2 + y1**2)
    return numerator / denominator

def create_maestro_dict(necesidad):
  maestro = {}
  for key, value in necesidad.items():
    #[necesidad, plantado, id_lista]
    maestro[key] = [value, 0, 0]
  return maestro

def tiempo_a_base(actual):
  return float(df_dist.loc[actual, base]) * tiempo_x_km

def encuentra_poligono_cercano(actual, completados):
  opciones = df_dist.loc[actual, :].drop(completados)
  opciones = opciones.drop(base)
  cercano = opciones.idxmin()
  return cercano

def plantar_planta(actual, dict_pols, dict_a_plantar, orden_plantado):
    planta_a_plantar = orden_plantado[dict_pols[actual][2]]
    dict_a_plantar[planta_a_plantar] += 1
    dict_pols[actual][1] += 1
    if dict_pols[actual][2] == len(orden_plantado) - 1:
        dict_pols[actual][2] = 0
    else:
        dict_pols[actual][2] += 1
    return dict_a_plantar, dict_pols

def create_farthest_poligon_list(df_dist):
  farthest_poligon_dict = dict(df_dist.loc[base])
  del farthest_poligon_dict[base]
  sorted_farthest_poligon_dict = dict(sorted(farthest_poligon_dict.items(), key=lambda item: item[1], reverse=True))
  sorted_farthest_poligon_dict
  farthest_poligon_list = list(sorted_farthest_poligon_dict.keys())
  return farthest_poligon_list

def create_poligon_order(df_dist, completed_list):
    farthest_poligon_list = create_farthest_poligon_list(df_dist)
    poligon_order_dict = {}

    for poligon in farthest_poligon_list:
        if poligon not in completed_list:
            completed_list.append(poligon)

            candidates = [x for x in df_dist.loc[poligon].keys() if x != poligon]

            ordered_candidates = sorted(
                candidates,
                key=lambda x: perpendicular_distance(x, poligon)
            )

            for candidate in ordered_candidates:
                if candidate not in completed_list:
                    poligon_order_dict[poligon] = candidate
                    break

    return farthest_poligon_list, poligon_order_dict

def adaptacion_de_pedido(pedido_dict, survival_rate):
  for key, value in pedido_dict.items():
    old_value = value
    new_value = 0
    for i in range(value):
      if random.random() <= survival_rate:
        new_value += 1
    pedido_dict[key] = new_value
  return pedido_dict

def plan_days(dias, primer_dia_a_planear, num_dias_a_planear, pedido_num, fecha_inicio, denvio, dreposo, dplantable):
    # Conseguir el segmento de días a planear
    id_primer_dia_a_planear = list(dias.keys()).index(primer_dia_a_planear)
    id_ultimo_dia_a_planear = id_primer_dia_a_planear + num_dias_a_planear - 1

    # Planear todos los días del segmento
    for idx, day in enumerate(dias):
        if idx < id_primer_dia_a_planear:
            continue
        if idx > id_ultimo_dia_a_planear:
            break
        if dias[day]["info"]["day_status"] == "Dia Laboral" and dias[day]["plantable"] == []:
            pedido_num, dias = hacer_pedido(
                dias,
                fecha_inicio,
                dias[day]["info"]["day_id"],
                pedido_num,
                denvio,
                dreposo,
                dplantable
            )

    return dias, pedido_num

def hacer_pedido(day_dict, inicio, plantable_target_id, pedido_num, denvio, dreposo, dplantable):
    plantable_iter = 0
    i = 0
    plantable_target_id = plantable_target_id + plantable_iter
    while i < dplantable:
        if plantable_target_id - (dplantable + i) >= 0:
            dia_key = list_to_date(plantable_target_id - (dplantable + i), inicio) #Cambio de checar si es laboral el dia que se pide al dia que llega
            if day_dict[dia_key]["info"]["day_status"] == "Dia Laboral":
                day_dict, pedido_num = agregar_pedidio_a_dict(day_dict, inicio, i, pedido_num, plantable_target_id, denvio, dreposo, dplantable)
                return pedido_num, day_dict
            i += 1
        else:
            break
    return pedido_num, day_dict

def determine_completados(necesidad):
    return [key for key in necesidad if necesidad[key][0] == necesidad[key][1]]

def determine_orden(orden, completados):
    return [i for i in orden if i not in completados]

def add_dicts(a, b, op=operator.add):
    result = {}
    for k in a:
        if k in b:
            result[k] = op(a[k], b[k])
        else:
            result[k] = a[k]
    for k in b:
        if k not in a:
            result[k] = b[k]
    return result

def subtract_dicts(a, b, op=operator.sub):
    result = {}
    for k in a:
        if k in b:
            result[k] = op(a[k], b[k])
        else:
            result[k] = a[k]
    for k in b:
        if k not in a:
            result[k] = -b[k]
    return result

def sim_planting(necesidad, lista_plantas, lista_orden, dict_orden,
                 tiempo_jornada, tiempo_minimo_permitido, df_dist,
                 tiempo_de_plantacion, lista_poligonos, orden_plantado):

    from copy import deepcopy
    necesidad = deepcopy(necesidad)  # evita modificar el original

    lista_poligonos_completados = determine_completados(necesidad)
    lista_orden_a_plantar = determine_orden(lista_orden, lista_poligonos_completados)
    poligono_actual = base
    siguiente_poligono = ""
    dict_a_plantar = {i: 0 for i in lista_plantas}
    tiempo = 0

    while True:

        if len(lista_poligonos_completados) == len(lista_poligonos):
            break
        if tiempo >= tiempo_jornada:
            break

        if poligono_actual == base:
            if not lista_orden_a_plantar:
                break
            siguiente_poligono = lista_orden_a_plantar[0]
            dist_al_siguiente = df_dist.loc[base, siguiente_poligono]
            tiempo_al_siguiente = tiempo_x_km * dist_al_siguiente

            if (tiempo + tiempo_al_siguiente + tiempo_minimo_permitido <= tiempo_jornada):
                poligono_actual = siguiente_poligono
                tiempo += tiempo_al_siguiente
            else:
                break
        else:
            planta_a_plantar = orden_plantado[necesidad[poligono_actual][2]]
            needs_more = necesidad[poligono_actual][1] < necesidad[poligono_actual][0]

            if needs_more:
                dict_a_plantar, necesidad = plantar_planta(
                    poligono_actual, necesidad, dict_a_plantar, orden_plantado
                )
                tiempo += tiempo_de_plantacion

                if necesidad[poligono_actual][1] == necesidad[poligono_actual][0]:
                    lista_poligonos_completados.append(poligono_actual)
                    if lista_orden_a_plantar:
                        lista_orden_a_plantar.pop(0)
                    siguiente_poligono = dict_orden.get(poligono_actual, "")
                    if siguiente_poligono and siguiente_poligono not in lista_poligonos_completados:
                        dist_al_siguiente = df_dist.loc[base, siguiente_poligono]
                        tiempo_al_siguiente = tiempo_x_km * dist_al_siguiente

                        if (tiempo + tiempo_al_siguiente + tiempo_minimo_permitido <= tiempo_jornada):
                            tiempo += tiempo_al_siguiente
                            poligono_actual = siguiente_poligono
                        else:
                            break
                    else:
                        break
            else:
                siguiente_poligono = dict_orden.get(poligono_actual, "")
                if siguiente_poligono and siguiente_poligono not in lista_poligonos_completados:
                    dist_al_siguiente = df_dist.loc[base, siguiente_poligono]
                    tiempo_al_siguiente = tiempo_x_km * dist_al_siguiente
                    if (tiempo + tiempo_al_siguiente + tiempo_minimo_permitido <= tiempo_jornada):
                        tiempo += tiempo_al_siguiente
                        poligono_actual = siguiente_poligono
                    else:
                        break
                else:
                    break

    return dict_a_plantar, necesidad

def real_planting(necesidad, lista_plantas, lista_orden, dict_orden,
                  tiempo_jornada, tiempo_minimo_permitido, df_dist,
                  tiempo_de_plantacion, lista_poligonos, orden_plantado,
                  active_order_dicts, num_orden):

    lista_poligonos_completados = determine_completados(necesidad)
    lista_orden_a_plantar = determine_orden(lista_orden, lista_poligonos_completados)
    poligono_actual = base
    siguiente_poligono = ""
    dict_a_plantar = {i: 0 for i in lista_plantas}
    tiempo = 0

    while True:
        # Verifica si terminamos
        if len(lista_poligonos_completados) == len(lista_poligonos):
            break
        if tiempo >= tiempo_jornada:
            break

        # Si estamos en la base al inicio del día
        if poligono_actual == base:
            if not lista_orden_a_plantar:
                break
            siguiente_poligono = lista_orden_a_plantar[0]
            dist_al_siguiente = df_dist.loc[base, siguiente_poligono]
            tiempo_al_siguiente = tiempo_x_km * dist_al_siguiente

            # Validamos si hay tiempo para ir al primero
            if (tiempo + tiempo_al_siguiente + tiempo_minimo_permitido <= tiempo_jornada):
                poligono_actual = siguiente_poligono
                tiempo += tiempo_al_siguiente
            else:
                break  # No hay tiempo para salir siquiera
        else:
            planta_a_plantar = orden_plantado[necesidad[poligono_actual][2]]
            needs_more = necesidad[poligono_actual][1] < necesidad[poligono_actual][0]
            has_inventory = active_order_dicts[num_orden]["remaining"].get(planta_a_plantar, 0) > 0

            if needs_more and has_inventory:
                dict_a_plantar, necesidad = plantar_planta(poligono_actual, necesidad, dict_a_plantar, orden_plantado)
                tiempo += tiempo_de_plantacion
                active_order_dicts[num_orden]["remaining"][planta_a_plantar] -= 1
                active_order_dicts[num_orden]["planted"] += 1

                if necesidad[poligono_actual][1] == necesidad[poligono_actual][0]:
                    lista_poligonos_completados.append(poligono_actual)
                    if lista_orden_a_plantar:
                        lista_orden_a_plantar.pop(0)
                    siguiente_poligono = dict_orden.get(poligono_actual, "")
                    if siguiente_poligono and siguiente_poligono not in lista_poligonos_completados:
                        dist_al_siguiente = df_dist.loc[base, siguiente_poligono]
                        tiempo_al_siguiente = tiempo_x_km * dist_al_siguiente
                        if (tiempo + tiempo_al_siguiente + tiempo_minimo_permitido <= tiempo_jornada):
                            tiempo += tiempo_al_siguiente
                            poligono_actual = siguiente_poligono
                        else:
                            break  # No alcanza tiempo para siguiente
                    else:
                        break  # No hay siguiente polígono válido
            else:
                # Ya no hay plantas o ya está completo => ir al siguiente
                siguiente_poligono = dict_orden.get(poligono_actual, "")
                if siguiente_poligono and siguiente_poligono not in lista_poligonos_completados:
                    tiempo_al_siguiente = df_dist.loc[poligono_actual, siguiente_poligono]
                    if (tiempo + tiempo_al_siguiente + tiempo_minimo_permitido <= tiempo_jornada):
                        dist_al_siguiente = df_dist.loc[base, siguiente_poligono]
                        tiempo_al_siguiente = tiempo_x_km * dist_al_siguiente
                        poligono_actual = siguiente_poligono
                    else:
                        break
                else:
                    break  # Nada más que hacer hoy

    return dict_a_plantar, necesidad, active_order_dicts

    """
    ## Untested Functions
    """
def insertar_pedido(id_proveedor, fecha_pedido, id_grupo_pedido, costo_total, plantas_total, conexion):
    cursor = conexion.cursor()
    query = """
        INSERT INTO pedidos (id_proveedor, fecha_pedido, id_grupo_pedido, costo_total, plantas_total)
        VALUES (%s, %s, %s, %s, %s);
    """
    try:
        cursor.execute(query, (
            id_proveedor,
            fecha_pedido,
            id_grupo_pedido,
            costo_total,
            plantas_total
        ))
    except:
        print("Error inserting:", e, flush=True)

    conexion.commit()
    cursor.close()

def insertar_pedido_detalle(id_pedido, id_especie, cantidad, conexion):
    cursor = conexion.cursor()
    query = """
        INSERT INTO pedidos_detalle (id_pedido, id_especie, cantidad)
        VALUES (%s, %s, %s);
    """
    try:
        cursor.execute(query, (
            id_pedido,
            id_especie,
            cantidad
        ))
    except:
        print("Error inserting:", e, flush=True)

    conexion.commit()
    cursor.close()

def insertar_informacion_dia(
    fecha_dia,
    poligonos_inicial_dict,
    inventario_inicial_dict,
    grupo_orden_pedir,
    grupo_orden_recibir,
    plantado_dict,
    expirado_dict,
    poligonos_final_dict,
    inventario_final_dict,
    dia_resumen,
    lista_poligonos_completados,
    dia_de_semana,
    estatus_dia,
    planting_order_cost,
    total_planted,
    conexion
):
    import json
    cursor = conexion.cursor()

    query = """
        INSERT INTO informacion_dia (
            fecha_dia,
            poligonos_inicial_dict,
            inventario_inicial_dict,
            grupo_orden_pedir,
            grupo_orden_recibir,
            plantado_dict,
            expirado_dict,
            poligonos_final_dict,
            inventario_final_dict,
            dia_resumen,
            lista_poligonos_completados,
            dia_de_semana,
            estatus_dia,
            planting_order_cost,
            total_planted
        )
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """

    try:
        cursor.execute(query, (
            str(fecha_dia),
            json.dumps(poligonos_inicial_dict),
            json.dumps(inventario_inicial_dict),
            json.dumps(grupo_orden_pedir),
            json.dumps(grupo_orden_recibir),
            json.dumps(plantado_dict),
            json.dumps(expirado_dict),
            json.dumps(poligonos_final_dict),
            json.dumps(inventario_final_dict),
            json.dumps(dia_resumen),
            json.dumps(lista_poligonos_completados),
            dia_de_semana,
            estatus_dia,
            float(planting_order_cost),
            int(total_planted)
        ))
        conexion.commit()
    except Exception as e:
        print("Error inserting into informacion_dia:", e, flush=True)
    finally:
        cursor.close()

plantas_a_especie_id = {
    "Agave lechuguilla": 1,
    "Agave salmiana": 2,
    "Agave scabra": 3,
    "Agave striata": 4,
    "Opuntia cantabrigiensis": 5,
    "Opuntia engelmani": 6,
    "Opuntia robusta": 7,
    "Opuntia streptacanta": 8,
    "Prosopis laevigata": 9,
    "Yucca filifera": 10
}

proveedores_precios = {

    "Vivero": {
        "Prosopis laevigata": 26.5,
        "Yucca filifera": 26
    },

    "Moctezuma": {
        "Agave scabra": 26,
        "Agave striata": 26,
        "Opuntia cantabrigiensis": 17,
        "Opuntia robusta": 17
    },

    "Venado": {
        "Agave striata": 25,
        "Opuntia cantabrigiensis": 18,
        "Opuntia engelmani": 18,
        "Opuntia robusta": 18,
        "Opuntia streptacanta": 18
    },

    "Laguna seca": {
        "Agave lechuguilla": 26,
        "Agave salmiana": 26,
        "Agave scabra": 26,
        "Opuntia engelmani": 21,
        "Opuntia robusta": 18
    }
}

vivero_list = ["Prosopis laevigata", "Yucca filifera"]
venado_list = [ "Agave striata",  "Opuntia cantabrigiensis", "Opuntia engelmani", "Opuntia robusta", "Opuntia streptacanta"]
laguna_seca_list = ["Agave lechuguilla", "Agave salmiana", "Agave scabra"]

def orders_to_sql(order_num, order, fecha_pedido, pedido_counter, conexion):

      vivero_cost = 0
      venado_cost = 0
      laguna_seca_cost = 0

      vivero_plants = 0
      venado_plants = 0
      laguna_seca_plants = 0

      vivero_dict = {}
      venado_dict = {}
      laguna_seca_dict = {}

      for plant in vivero_list:
        vivero_plants += order[plant]
        plant_cost = order[plant] * proveedores_precios["Vivero"][plant]
        vivero_cost += plant_cost
        vivero_dict[plant] = order[plant]

      for plant in venado_list:
        venado_plants += order[plant]
        plant_cost = order[plant] * proveedores_precios["Venado"][plant]
        venado_cost += plant_cost
        venado_dict[plant] = order[plant]

      for plant in laguna_seca_list:
        laguna_seca_plants += order[plant]
        plant_cost = order[plant] * proveedores_precios["Laguna seca"][plant]
        laguna_seca_cost += plant_cost
        laguna_seca_dict[plant] = order[plant]

      if vivero_cost != 0:
        vivero_cost += 4500
        # func here
        insertar_pedido(
            id_proveedor = 1,
            fecha_pedido=str(fecha_pedido),
            id_grupo_pedido=order_num,
            costo_total=vivero_cost,
            plantas_total=vivero_plants,
            conexion=conexion
        )

        pedido_counter += 1
        for plant in vivero_dict:
            id_especie = plantas_a_especie_id[plant]
            cantidad = vivero_dict[plant]
            insertar_pedido_detalle(
                id_pedido = pedido_counter,
                id_especie = id_especie,
                cantidad = cantidad,
                conexion = conexion
            )

      if venado_cost != 0:
        venado_cost += 4500
        insertar_pedido(
            id_proveedor = 3,
            fecha_pedido=str(fecha_pedido),
            id_grupo_pedido=order_num,
            costo_total=venado_cost,
            plantas_total=venado_plants,
            conexion=conexion
        )

        pedido_counter += 1
        for plant in venado_dict:
            id_especie = plantas_a_especie_id[plant]
            cantidad = venado_dict[plant]
            insertar_pedido_detalle(
                id_pedido = pedido_counter,
                id_especie = id_especie,
                cantidad = cantidad,
                conexion = conexion
            )

      if laguna_seca_cost != 0:
        laguna_seca_cost += 4500
        insertar_pedido(
            id_proveedor = 4,
            fecha_pedido=str(fecha_pedido),
            id_grupo_pedido=order_num,
            costo_total=laguna_seca_cost,
            plantas_total=laguna_seca_plants,
            conexion=conexion
        )

        pedido_counter += 1
        for plant in laguna_seca_dict:
            id_especie = plantas_a_especie_id[plant]
            cantidad = laguna_seca_dict[plant]
            insertar_pedido_detalle(
                id_pedido = pedido_counter,
                id_especie = id_especie,
                cantidad = cantidad,
                conexion = conexion
            )

      total_cost = 0
      total_cost = venado_cost + vivero_cost + laguna_seca_cost
      total_plantado = 0
      total_plantado = venado_plants + vivero_plants + laguna_seca_plants

      return pedido_counter, total_cost, total_plantado

def determinar_inventario(active_orders, plant_list):

    inventory = {plant: 0 for plant in plant_list}

    for order_id, order in active_orders.items():  # Loop through dict properly
        for plant in order.get("remaining", {}):
            inventory[plant] += order["remaining"][plant]

    return inventory

"""# 3. Inicializar Parametros

## 3.1 Params Estandar
"""

initial_days_to_plan = 7
days_to_create = 720
base = "18"

"""## 3.2 Params *Booleanas*"""

bool_plants_randomly_die = False

"""## 3.3 Params Poligonos/Plantas"""

orden_plantado = [
    "Agave salmiana",
    "Prosopis laevigata",
    "Opuntia streptacanta",
    "Agave salmiana",
    "Opuntia robusta",
    "Agave scabra",
    "Opuntia cantabrigiensis",
    "Agave salmiana",
    "Opuntia engelmani",
    "Agave striata",
    "Agave salmiana",
    "Opuntia engelmanii",
    "Prosopis laevigata",
    "Agave salmiana",
    "Opuntia cantabrigiensis",
    "Agave lechuguilla",
    "Agave salmiana",
    "Opuntia streptacantha",
    "Yucca filifera",
    "Agave salmiana",
    "Opuntia robusta",
    "Prosopis laevigata"
]

lista_plantas = list(set(orden_plantado))

plantas_por_ha = 524

ha_por_poligono = {
    "1": 5.40,
    "3": 8.00,
    "4": 8.00,
    "5": 7.56,
    "20": 1.38,
    "23": 5.53,
    "24": 5.64,
    #18: 7.11,
    "17": 6.11,
    "16": 5.64,
    "19": 4.92,
    "25": 5.05,
    "26": 4.75
}

dict_necesidad_poligonos = {
    poligono: round(ha * plantas_por_ha)
    for poligono, ha in ha_por_poligono.items()
}

coords = [
    (2041.3843813852268, 1523.4630780715959),  # 1
    (2229.5467678433433, 1402.7551320418606),  # 2
    (2442.5607902487586, 1296.2481208391532),  # 3
    (2641.373877827146, 1470.2095724702422),   # 4
    (2836.6367316987757, 1683.223594875657),   # 5
    (3053.200987810948, 1896.237617281072),    # 6
    (2041.3843813852268, 426.4408626837087),   # 7
    (1842.5712938068395, 422.8906289769517),   # 8
    (1629.557271401425, 429.9910963904657),    # 9
    (1938.4276038892765, 167.27380209045364),  # 10
    (1842.5712938068395, 1583.8170510864634),  # 11
    (1640.2079725216952, 1590.9175184999772),  # 12
    (1118.3236176284288, 1541.2142466053801)   # 13
]

plotted_to_real = {
    1: 18,
    2: 17,
    3: 16,
    4: 19,
    5: 25,
    6: 26,
    7: 5,
    8: 4,
    9: 3,
    10: 1,
    11: 24,
    12: 23,
    13: 20
}

# Get reference (point 1) and shift all
ref_x, ref_y = coords[0]
shifted = [(x - ref_x, y - ref_y) for (x, y) in coords]

# Create dict_centroide_poligonos
dict_centroide_poligonos = {
    str(plotted_to_real[i + 1]): (x, y)
    for i, (x, y) in enumerate(shifted)
}

df_dist = create_dist_df(dict_centroide_poligonos)

"""# 4. Correr Modelo

## 4.1 Legacy
"""

if __name__ == "__main__":

    start_date = sys.argv[1]  # Format: YYYY-MM-DD
    tiempo_jornada = int(sys.argv[2])  # In hours
    denvio = int(sys.argv[3])
    dreposo = int(sys.argv[4])
    dplantable = int(sys.argv[5])
    velocidad_promedio = float(sys.argv[6])  # km/h
    tiempo_de_plantacion = float(sys.argv[7])   # min/planta
    tiempo_minimo_permitido = int(sys.argv[8])  # min
    plant_survival_rate = float(sys.argv[9])

    print("Connecting to DB...", flush=True)

    try:
        conexion = mysql.connector.connect(
            host="127.0.0.1",  # Try loopback explicitly
            user="root",
            password="root",
            database="reforestacion",
            port=3306
        )

        if conexion.is_connected():
            print("Connection successful!", flush=True)

    except Exception as e:
        print(f"General exception: {e}", flush=True)
        traceback.print_exc(file=sys.stdout)

    #Ajuste de unos params
    tiempo_jornada *= 60
    tiempo_x_km = 60/velocidad_promedio
    tiempo_x_km = tiempo_x_km / 1000 #currently doing everything by meters

    # Procesar fecha inicial
    plan_start_date = datetime.strptime(start_date, '%d-%m-%Y').date()

    current_date = plan_start_date - timedelta(days=1)
    planned_date = plan_start_date

    # Crear estructuras base
    lista_poligonos = list(dict_necesidad_poligonos.keys())
    lista_poligonos_completados = []
    projected_poligons = create_maestro_dict(dict_necesidad_poligonos)
    real_poligons = create_maestro_dict(dict_necesidad_poligonos)
    farthest_poligon_list, poligon_order_dict = create_poligon_order(df_dist, lista_poligonos_completados)

    dias = create_dates(start_date, days_to_create)
    pedido_num = 0
    pedido_counter = 0 #should get a new name for this but oh well

    # Diccionarios y listas auxiliares
    inventory = {i: 0 for i in lista_plantas}
    storage = {i: 0 for i in lista_plantas}
    historical_orders_dict = {}
    active_order_dicts = {}
    master_plant_dicts = {}
    real_by_order_list = []
    real_by_day_list = []
    projection_by_order_list = []

    day_plant_dict = {}

    #sql stuff
    sql_grupo_orden_pedir = ""
    sql_grupo_orden_recibir = ""
    sql_expirado_dict = {}
    sql_inventario_final = {}
    total_planting_cost = 0
    total_planted = 0

    # Plan inicial
    dias, pedido_num = plan_days(dias, planned_date, initial_days_to_plan, pedido_num, start_date, denvio, dreposo, dplantable)
    planned_date += timedelta(days=initial_days_to_plan)

    # Bucle principal de planeación
    while lista_poligonos != lista_poligonos_completados:

        # Reset variables per day
        sql_grupo_orden_pedir = 0
        sql_grupo_orden_recibir = 0
        sql_expirado_dict = {}
        sql_inventario_final = {}
        total_planting_cost = 0
        total_planted = 0

        order_dict = {}
        delete_from_active_list = []
        jornada_laboral = tiempo_jornada

        sql_poligonos_inicial = real_poligons.copy()

        # Advance a day
        current_date += timedelta(days=1)
        planned_date += timedelta(days=1)

        # Skip if day doesn't exist
        if current_date not in dias:
            break

        sql_dia_de_semana = dias[current_date]['info']['day_name']
        sql_estatus_dia = dias[current_date]['info']['day_status']

        # Plan additional day
        dias, pedido_num = plan_days(dias, planned_date, 1, pedido_num, start_date, denvio, dreposo, dplantable)

        sql_inventario_inicial = determinar_inventario(active_order_dicts, lista_plantas)

        # Check for expired orders
        for order in list(active_order_dicts.keys()):
            if active_order_dicts[order]["expiry_date"] == current_date:
                sql_expirado_dict = order
                delete_from_active_list.append(order)
        for order in delete_from_active_list:
            del active_order_dicts[order]

        # Simulate planting projection if there's a new order
        if dias[current_date]['order']:
            order_num = dias[current_date]['order'][0]
            sql_grupo_orden_pedir = order_num

            sim_days = [
                date for date in dias
                if order_num in dias[date]['plantable'] and dias[date]['info']['day_status'] == "Dia Laboral"
            ]

            for sim_day in sim_days:
                jornada_sim = tiempo_jornada * 0.5 if dias[sim_day]['info']['day_name'] == "Sabado" else tiempo_jornada
                day_order_dict, projected_poligons = sim_planting(
                    projected_poligons,
                    lista_plantas,
                    farthest_poligon_list,
                    poligon_order_dict,
                    jornada_sim,
                    tiempo_minimo_permitido,
                    df_dist,
                    tiempo_de_plantacion,
                    lista_poligonos,
                    orden_plantado
                )
                order_dict = add_dicts(order_dict, day_order_dict) if order_dict else day_order_dict

            pedido_counter, total_planting_cost, total_planted = orders_to_sql(
                order_num, order_dict, current_date, pedido_counter, conexion
            )
            historical_orders_dict[order_num] = order_dict

        # Order arrives into inventory
        if dias[current_date]['order_arrival']:
            order_num = dias[current_date]['order_arrival'][0]
            sql_grupo_orden_recibir = order_num
            order = historical_orders_dict.get(order_num, {})
            expiry_date = current_date + timedelta(days=dreposo + dplantable)
            active_order_dicts[order_num] = {
                "expiry_date": expiry_date,
                "status": "storage" if dreposo > 0 else "inventory",
                "remaining": order,
                "planted": 0
            }

        # Reset daily planting record
        day_plant_dict = {i: 0 for i in lista_plantas}

        # Real planting if scheduled
        if dias[current_date]['plantable']:
            order_num = dias[current_date]['plantable'][0]
            previous_date = current_date - timedelta(days=1)

            if order_num in dias[previous_date]['order_inventory']:
                remaining = active_order_dicts[order_num]["remaining"]
                if bool_plants_randomly_die:
                    remaining = adaptacion_de_pedido(remaining, plant_survival_rate)
                active_order_dicts[order_num]["remaining"] = remaining
                active_order_dicts[order_num]["status"] = "inventory"

            if dias[current_date]['info']['day_status'] == "Dia Laboral":
                jornada_real = tiempo_jornada * 0.5 if dias[current_date]['info']['day_name'] == "Sabado" else tiempo_jornada
                day_plant_dict, real_poligons, active_order_dicts = real_planting(
                    real_poligons,
                    lista_plantas,
                    farthest_poligon_list,
                    poligon_order_dict,
                    jornada_real,
                    tiempo_minimo_permitido,
                    df_dist,
                    tiempo_de_plantacion,
                    lista_poligonos,
                    orden_plantado,
                    active_order_dicts,
                    order_num
                )
                master_plant_dicts[current_date] = day_plant_dict

        lista_poligonos_completados = determine_completados(real_poligons)
        sql_inventario_final = determinar_inventario(active_order_dicts, lista_plantas)
        sql_dia_resumen = "Falta implementar..."
        total_planted = sum(day_plant_dict.values())

        insertar_informacion_dia(
            current_date,
            sql_poligonos_inicial,
            sql_inventario_inicial,
            sql_grupo_orden_pedir,
            sql_grupo_orden_recibir,
            day_plant_dict,
            sql_expirado_dict,
            real_poligons,
            sql_inventario_final,
            sql_dia_resumen,
            lista_poligonos_completados,
            sql_dia_de_semana,
            sql_estatus_dia,
            total_planting_cost,
            total_planted,
            conexion
        )

    print("\nCompleted planting...")
    print("Polígonos plantados:", real_poligons)
    conexion.close()
