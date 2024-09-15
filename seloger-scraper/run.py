import asyncio
import json
from pathlib import Path
import seloger

output = Path(__file__).parent / "results"
output.mkdir(exist_ok=True)

base_url = "https://www.seloger.com"

async def fetch_property_details(url):
    full_url = f"{base_url}{url}"
    details = await seloger.scrape_property(url=full_url)
    return details

async def run():
    search_file = output.joinpath("search.json")
    
    # Vérifier si le fichier search.json existe et n'est pas vide
    if not search_file.exists() or search_file.stat().st_size == 0:
        print("Le fichier search.json est vide ou n'existe pas. Scraping des données...")
        search_data = await seloger.scrape_search(
            url="https://www.seloger.com/immobilier/pays/achat/bien-maison/france.htm?projects=2&types=2&places=[{%22countries%22:[250]}]&mandatorycommodities=0&privateseller=1&enterprise=0&qsVersion=1.0&m=search_refine-redirection-search_results",
            scrape_all_pages=False,
            max_pages=2,
        )
        # Sauvegarder les données récupérées dans search.json
        with open(search_file, "w", encoding="utf-8") as file:
            json.dump(search_data, file, indent=2, ensure_ascii=False)
    else:
        # Charger les résultats de la recherche depuis le fichier JSON
        try:
            with open(search_file, "r", encoding="utf-8") as file:
                search_data = json.load(file)
        except json.JSONDecodeError as e:
            print(f"Erreur lors du chargement du JSON : {e}")
            return

    # Liste pour contenir tous les détails des propriétés
    all_property_details = []

    # Boucler sur les deux premières propriétés et récupérer leurs détails
    for property in search_data[:2]:  # Limite à 2 propriétés
        classified_url = property.get("classifiedURL")
        if classified_url:
            details = await fetch_property_details(classified_url)
            all_property_details.append(details)

    # Sauvegarder les données détaillées dans un autre fichier JSON
    with open(output.joinpath("detailed_properties.json"), "w", encoding="utf-8") as file:
        json.dump(all_property_details, file, indent=2, ensure_ascii=False)

if __name__ == "__main__":
    asyncio.run(run())
