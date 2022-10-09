from OsmPbfDownloadDAG import OsmPbfDownloadDAG
from OemDbInitDAG import OemDbInitDAG
from airflow.models import DAG



download_nord_ovest_pbf = OsmPbfDownloadDAG(
    dag_id = "download-italy-nord-ovest-latest",
    schedule_interval=None,
    pbf_url = "http://download.geofabrik.de/europe/italy/nord-ovest-latest.osm.pbf",
    prefix = "nord-ovest"
)

download_nord_ovest_html = OsmPbfDownloadDAG(
    dag_id = "download-italy-nord-ovest-from-html",
    schedule_interval=None,
    html_url="http://download.geofabrik.de/europe/italy/",
    prefix="nord-ovest"
)

init_nord_ovest_pbf = OemDbInitDAG(
    dag_id="db-init-italy-nord-ovest-latest",
    schedule_interval=None,
    pbf_url="http://download.geofabrik.de/europe/italy/nord-ovest-latest.osm.pbf"
)

init_nord_ovest_html = OemDbInitDAG(
    dag_id="db-init-italy-nord-ovest-from-html",
    schedule_interval=None,
    html_url="http://download.geofabrik.de/europe/italy/",
    prefix="nord-ovest"
)
