# Dashboard Interactive Components

## Overview

Modul Cost Center Management menyediakan 3 Livewire components interaktif untuk dashboard yang mendukung drill-down functionality dan real-time updates.

## Components

### 1. CostDistributionChart

**Purpose:** Menampilkan distribusi biaya per kategori dalam bentuk pie chart dengan drill-down ke detail transaksi.

**Features:**
- Pie chart interaktif dengan Chart.js
- Click pada chart atau tabel untuk drill-down ke detail kategori
- Menampilkan daftar transaksi per kategori
- Summary total biaya dan jumlah transaksi
- Responsive terhadap perubahan periode

**Usage:**
```blade
@livewire('cost-center-management::cost-distribution-chart', [
    'costCenterId' => $costCenter->id,
    'year' => $year,
    'month' => $month
])
```

**Drill-Down Flow:**
1. User melihat pie chart dengan distribusi biaya per kategori
2. User click pada kategori (chart atau tabel)
3. Component menampilkan detail transaksi untuk kategori tersebut
4. User dapat kembali ke overview dengan tombol "Back to Overview"

### 2. TrendLineChart

**Purpose:** Menampilkan trend analysis budget vs actual dalam bentuk line chart dengan drill-down ke variance analysis per periode.

**Features:**
- Line chart interaktif dengan multiple view modes (Budget & Actual, Budget Only, Actual Only, Variance Only)
- Configurable time range (6, 12, 24 months)
- Click pada chart atau tabel untuk drill-down ke detail periode
- Menampilkan variance analysis per kategori untuk periode yang dipilih
- Responsive terhadap perubahan periode

**Usage:**
```blade
@livewire('cost-center-management::trend-line-chart', [
    'costCenterId' => $costCenter->id,
    'months' => 12
])
```

**Drill-Down Flow:**
1. User melihat trend chart untuk 6/12/24 bulan terakhir
2. User memilih view mode (both, budget, actual, variance)
3. User click pada periode tertentu (chart atau tabel)
4. Component menampilkan variance analysis per kategori untuk periode tersebut
5. User dapat kembali ke chart dengan tombol "Back to Chart"

### 3. BudgetVarianceChart

**Purpose:** Menampilkan perbandingan budget vs actual per kategori dalam bentuk bar chart dengan drill-down ke detail transaksi.

**Features:**
- Bar chart interaktif dengan Chart.js
- Click pada kategori untuk drill-down ke detail transaksi
- Menampilkan utilization percentage dengan color coding
- Summary cards untuk budget, actual, variance, dan utilization
- Status indicator (low, normal, warning, critical)
- Responsive terhadap perubahan periode

**Usage:**
```blade
@livewire('cost-center-management::budget-variance-chart', [
    'costCenterId' => $costCenter->id,
    'year' => $year,
    'month' => $month
])
```

**Drill-Down Flow:**
1. User melihat bar chart dengan budget vs actual per kategori
2. User click pada kategori (chart atau tabel)
3. Component menampilkan summary cards dan detail transaksi untuk kategori tersebut
4. User dapat kembali ke overview dengan tombol "Back to Overview"

## Event Communication

Components mendukung Livewire events untuk komunikasi antar component:

**Event: `periodChanged`**
- Dispatched ketika user mengubah periode (year/month)
- Payload: `{ year: int, month: int }`
- Semua chart components akan update data sesuai periode baru

**Example:**
```javascript
Livewire.dispatch('periodChanged', { year: 2026, month: 2 });
```

## Color Coding

### Category Colors (Pie Chart & Bar Chart)
- Personnel: Blue (`rgba(59, 130, 246, 0.8)`)
- Supplies: Green (`rgba(16, 185, 129, 0.8)`)
- Services: Amber (`rgba(245, 158, 11, 0.8)`)
- Depreciation: Red (`rgba(239, 68, 68, 0.8)`)
- Overhead: Purple (`rgba(139, 92, 246, 0.8)`)
- Other: Gray (`rgba(107, 114, 128, 0.8)`)

### Utilization Status Colors
- Low (<50%): Blue
- Normal (50-80%): Green
- Warning (80-100%): Yellow
- Critical (>100%): Red

### Variance Classification Colors
- Favorable (under budget): Green
- Unfavorable (over budget): Red

## Dependencies

- **Livewire v3**: Framework untuk reactive components
- **Chart.js v4.4.0**: Library untuk rendering charts
- **Tailwind CSS**: Styling framework
- **Alpine.js**: Included dengan Livewire untuk interactivity

## Performance Considerations

1. **Data Caching**: Chart data di-cache di component level untuk menghindari query berulang
2. **Lazy Loading**: Drill-down data hanya di-load ketika user click
3. **Efficient Queries**: Menggunakan eager loading dan aggregate queries
4. **Client-side Rendering**: Chart rendering dilakukan di client untuk performance

## Browser Compatibility

Components telah ditest dan kompatibel dengan:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Troubleshooting

### Chart tidak muncul
- Pastikan Chart.js sudah di-load: `<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>`
- Check browser console untuk error JavaScript

### Drill-down tidak berfungsi
- Pastikan Livewire scripts sudah di-load
- Check network tab untuk error AJAX requests

### Data tidak update setelah period change
- Pastikan event `periodChanged` di-dispatch dengan benar
- Check Livewire listeners di component

## Future Enhancements

1. Export chart sebagai image (PNG/SVG)
2. Print-friendly view
3. Comparison mode (compare multiple cost centers)
4. Custom date range selector
5. Real-time updates dengan WebSocket
6. Mobile-optimized touch gestures
