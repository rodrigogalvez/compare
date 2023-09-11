# compare
Comparar dos carpetas en PHP

# Problema
Para un proyecto cuya gestión ha sido muy desordenada, se han hecho cambios locales tanto en el servidor de producción como el de QAS en diferentes momentos.

Convenimos en definir la versión de productivo como la versión "actual" y reemplazar las versiones en Azure y QAS con esta. Sin embargo, como las modificaciones locales se han hecho en cualquier momento y hay configuraciones específicas para la base de datos de QAS y de productivo, los archivos deben compararse contra la versión "actual", homologando el código fuente en vez de reemplazar el código local con el código de productivo.

# Diagnóstico
Los cambios se han hecho en momentos diferentes. Al acceder al servidor de QAS se encuentra abierto el editor de texto Notepad++ y desconocemos los cambios realizados.

Para buscar las diferencias en los archivos, estos deben ser comparados por tamaño y contenido pero no por fecha de modificación.

Además existen varios archivos en productivo que corresponden a cargas del momento (archivos Excel, EDI, XCBL y otros).

# Solución
Crear un programa que compare la carpeta del repositorio del proyecto contra la carpeta publicada en QAS, indicando cuales archivos son diferentes en tamaño, cuáles no están en la carpeta "actual" para borrarlos y cuáles son nuevos para crearlos.

# Entregable
El programa publicado presenta un listado de diferencias entre los acrhivos de la carpeta del proyecto y al versión en uso en QAS. Después se utilizará en producción para realizar la homologación final. Las diferencias son presentadas como comandos de DOS ya que los servidores corren sobre Windows Server. Los comandos están implementados para renombrar cualquier archivo que debe ser reemplazado antes de realizar efectivamente el reemplazo.

# Próximos cambios
- [x] Los archivos que se eliminan deben ser renombrados y marcados como eliminados.
- [ ] Obtener una comparación de los archivos línea a línea como el comando FC de Windows antes de decidir qué cambios realizar.