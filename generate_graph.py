import sys
import pandas as pd
import matplotlib.pyplot as plt

graph_filename = sys.argv[1]
csv_filename = sys.argv[2]

print(f"Graph filename: {graph_filename}")
print(f"CSV filename: {csv_filename}")

df = pd.read_csv(csv_filename, parse_dates=['Datum_Inschrijving'], quoting=0, quotechar='"')

# Convert dates using period_range instead of datetime
df['Datum_Inschrijving'] = pd.PeriodIndex(df['Datum_Inschrijving'], freq='D')

# Create a histogram using the period index
plt.figure(figsize=(40, 20))
plt.hist(df['Datum_Inschrijving'].astype(str), bins=50)
plt.title('Registration Dates Distribution', fontsize=36)
plt.xlabel('Date', fontsize=25)
plt.ylabel('Frequency', fontsize=25)

ax = plt.gca()
# Get all tick locations
all_ticks = ax.get_xticks()
# Show only every nth tick to reduce density (adjust n as needed)
n = max(1, len(all_ticks) // 25)  # Will show approximately 10 ticks
ax.set_xticks(all_ticks[::n])
plt.xticks(rotation=90)
plt.savefig(graph_filename)
