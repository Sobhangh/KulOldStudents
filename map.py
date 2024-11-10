import sys
import pandas as pd
import matplotlib.pyplot as plt
import matplotlib.pyplot as plt
import geopandas as gpd


def get_city_coordinates_from_files():
    coordinates = {}
    
    # List of country files to process
    countries = ['countries/NL.txt', 'countries/BE.txt', 'countries/FR.txt', 'countries/DE.txt','countries/LU.txt']
    
    for country_file in countries:
        cities_db = pd.read_csv(country_file, sep='\t', header=None,
                              names=['id', 'name', 'asciiname', 'alternatenames',
                                    'latitude', 'longitude', 'feature_class',
                                    'feature_code', 'country', 'cc2', 'admin1',
                                    'admin2', 'admin3', 'admin4', 'population',
                                    'elevation', 'dem', 'timezone', 'modification'])
        
        # Filter for cities/towns
        cities_db = cities_db[cities_db['feature_class'] == 'P']
        
        # Add to coordinates dictionary
        coordinates.update(dict(zip(cities_db['name'],
                                  zip(cities_db['latitude'], cities_db['longitude']))))
        
        # Add alternate names
        """for _, row in cities_db.iterrows():
            if isinstance(row['alternatenames'], str):
                for alt_name in row['alternatenames'].split(','):
                    if alt_name:
                        coordinates[alt_name] = (row['latitude'], row['longitude'])"""
    
    return coordinates

# Read and process your existing CSV
graph_filename = sys.argv[1]
csv_filename = sys.argv[2]

print(f"Graph filename: {graph_filename}")
print(f"CSV filename: {csv_filename}")

df = pd.read_csv(csv_filename, parse_dates=['Datum_Inschrijving'], quoting=0, quotechar='"')

# Get city coordinates
city_coords = get_city_coordinates_from_files()

# Create a new figure
plt.figure(figsize=(50, 50))

# Load world map and filter for Western Europe
world = gpd.read_file("geomap/medium_quality/ne_50m_admin_0_countries.shp")  # Medium resolution (1:50 million)
#print("world columns: ....................")
#print(world.columns)
western_europe = world[world['NAME'].isin(['Netherlands', 'Belgium', 'France', 'Germany', 'Luxembourg'])]

# Plot the map
ax = western_europe.plot(ax=plt.gca(), color='whitesmoke')

# Set map boundaries (adjust these coordinates as needed)
plt.xlim([-5, 15])  # longitude
plt.ylim([40, 55])  # latitude

# Plot cities with their frequencies
data_city_counts = df['Herkomst_actuele_Schrijfwijze'].value_counts()
city_counts = data_city_counts.to_dict()
#top_count = data_city_counts.quantile(0.95)
#print("count:.......   ")
#print(top_count)

for city, count in city_counts.items():
    #print(city)
    #count = city_counts.get(city, 0)
    coordinates = city_coords.get(city,None)
    if coordinates is not None:
        #if coordinates[0] < 49.5:
            #print(city)
            #print(coordinates)
        lat, lon = coordinates
        size = max(100, count * 10)  # Minimum size of 100, scales up with count
        plt.scatter(lon, lat, c='red', s=size)
        #if count >= top_count:
    #    plt.annotate(f"{city} ({count})", (lon, lat), xytext=(5, 5), textcoords='offset points', fontsize=10)

plt.title('City Distribution', fontsize=24)

# Add country borders
western_europe.boundary.plot(ax=ax, color='black', linewidth=1)

plt.savefig(graph_filename, bbox_inches='tight', dpi=300)