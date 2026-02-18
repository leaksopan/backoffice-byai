# Cost Center Dashboard API Documentation

## Overview
CostCenterDashboardController menyediakan real-time cost monitoring, budget vs actual visualization, dan variance analysis charts untuk cost centers.

## Routes

### Web Routes

#### 1. Dashboard Overview
```
GET /m/cost-center-management/dashboard
Route: ccm.dashboard.index
```
Menampilkan overview semua cost centers dengan summary metrics.

**Query Parameters:**
- `type` (optional): Filter by cost center type (medical, non_medical, administrative, profit_center)
- `classification` (optional): Filter by classification

**Response:** View dengan data:
- `costCenters`: Collection of cost centers
- `summaryMetrics`: Array dengan total budget, actual, variance, utilization
- `currentYear`, `currentMonth`: Current period

#### 2. Cost Center Detail Dashboard
```
GET /m/cost-center-management/dashboard/{costCenter}
Route: ccm.dashboard.show
```
Menampilkan detail dashboard untuk specific cost center dengan charts.

**Query Parameters:**
- `year` (optional): Fiscal year (default: current year)
- `month` (optional): Period month (default: current month)

**Response:** View dengan data:
- `costCenter`: Cost center model
- `variances`: Variance analysis by category
- `trends`: 12-month trend data
- `budgetSummary`: Budget summary metrics
- `recentTransactions`: Last 10 transactions

### AJAX Routes

#### 3. Real-Time Monitoring
```
GET /m/cost-center-management/dashboard/{costCenter}/real-time
Route: ccm.dashboard.real-time
```
Mendapatkan real-time cost monitoring data.

**Query Parameters:**
- `year` (optional): Fiscal year
- `month` (optional): Period month

**Response JSON:**
```json
{
  "success": true,
  "data": {
    "cost_center": {
      "id": 1,
      "code": "CC001",
      "name": "Rawat Jalan",
      "type": "medical"
    },
    "period": {
      "year": 2026,
      "month": 2,
      "start": "2026-02-01",
      "end": "2026-02-28"
    },
    "utilization": {
      "personnel": {
        "actual": 50000000,
        "budget": 60000000,
        "remaining": 10000000,
        "percentage": 83.33,
        "status": "warning"
      },
      ...
    },
    "summary": {
      "total_actual": 150000000,
      "total_budget": 180000000,
      "total_remaining": 30000000,
      "total_percentage": 83.33,
      "status": "warning"
    },
    "timestamp": "2026-02-16T08:30:00+07:00"
  }
}
```

**Status Values:**
- `low`: < 50%
- `normal`: 50-80%
- `warning`: 80-100%
- `critical`: > 100%

#### 4. Budget vs Actual
```
GET /m/cost-center-management/dashboard/{costCenter}/budget-vs-actual
Route: ccm.dashboard.budget-vs-actual
```
Mendapatkan budget vs actual comparison data untuk charts.

**Response JSON:**
```json
{
  "success": true,
  "data": {
    "cost_center": {...},
    "period": {...},
    "variances": {
      "personnel": {
        "budget": 60000000,
        "actual": 50000000,
        "variance": -10000000,
        "variance_percentage": -16.67,
        "classification": "favorable"
      },
      ...
    },
    "chart_data": {
      "labels": ["Personnel", "Supplies", ...],
      "budget": [60000000, 30000000, ...],
      "actual": [50000000, 28000000, ...],
      "variance": [-10000000, -2000000, ...]
    }
  }
}
```

#### 5. Variance Analysis
```
GET /m/cost-center-management/dashboard/{costCenter}/variance-analysis
Route: ccm.dashboard.variance-analysis
```
Mendapatkan trend analysis data untuk variance charts.

**Query Parameters:**
- `months` (optional): Number of months (default: 12)

**Response JSON:**
```json
{
  "success": true,
  "data": {
    "cost_center": {...},
    "trends": [
      {
        "period": "2025-03",
        "period_label": "Mar 2025",
        "budget": 180000000,
        "actual": 175000000,
        "variance": -5000000,
        "variance_percentage": -2.78,
        "classification": "favorable"
      },
      ...
    ],
    "chart_data": {
      "labels": ["Mar 2025", "Apr 2025", ...],
      "budget": [180000000, 185000000, ...],
      "actual": [175000000, 180000000, ...],
      "variance": [-5000000, -5000000, ...],
      "variance_percentage": [-2.78, -2.70, ...]
    }
  }
}
```

#### 6. Cost Distribution
```
GET /m/cost-center-management/dashboard/{costCenter}/cost-distribution
Route: ccm.dashboard.cost-distribution
```
Mendapatkan cost distribution by category untuk pie chart.

**Response JSON:**
```json
{
  "success": true,
  "data": {
    "cost_center": {...},
    "period": {...},
    "total_cost": 150000000,
    "distribution": [
      {
        "category": "personnel",
        "total": 50000000
      },
      ...
    ],
    "chart_data": {
      "labels": ["Personnel", "Supplies", ...],
      "values": [50000000, 28000000, ...],
      "percentages": [33.33, 18.67, ...]
    }
  }
}
```

## Authorization

Semua routes menggunakan Policy `CostCenterPolicy`:

- `view`: User dapat view cost center jika:
  - Memiliki permission `cost-center-management.view-all` (admin), atau
  - Memiliki permission `cost-center-management.view` DAN adalah manager dari cost center tersebut

## Usage Examples

### JavaScript - Real-Time Monitoring
```javascript
async function updateRealTimeData(costCenterId) {
    const response = await fetch(`/m/cost-center-management/dashboard/${costCenterId}/real-time?year=2026&month=2`);
    const data = await response.json();
    
    if (data.success) {
        updateDashboard(data.data);
    }
}

// Auto-refresh every 30 seconds
setInterval(() => updateRealTimeData(1), 30000);
```

### JavaScript - Chart.js Integration
```javascript
// Budget vs Actual Chart
const response = await fetch(`/m/cost-center-management/dashboard/${costCenterId}/budget-vs-actual`);
const data = await response.json();

const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: data.data.chart_data.labels,
        datasets: [
            {
                label: 'Budget',
                data: data.data.chart_data.budget,
                backgroundColor: 'rgba(59, 130, 246, 0.5)'
            },
            {
                label: 'Actual',
                data: data.data.chart_data.actual,
                backgroundColor: 'rgba(239, 68, 68, 0.5)'
            }
        ]
    }
});
```

## Requirements Validation

Task 17.1 memenuhi requirements:
- **8.2**: Dashboard kinerja per responsibility center dengan metrik (Total Cost, Budget Variance, Efficiency Ratio) ✓
- **8.5**: Notifikasi ketika variance melebihi threshold (via status indicators) ✓

## Notes

- Semua monetary values dalam Rupiah (IDR)
- Timestamps menggunakan ISO 8601 format
- Charts menggunakan Chart.js v4.4.0
- Real-time monitoring dapat di-refresh via AJAX tanpa reload page
- Row-level security diterapkan untuk cost center managers
