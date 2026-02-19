# Risk Assessment Engine: Quantitative Basis for Alerts

The Risk Engine is a central component of the StarWeather system, responsible for converting raw telemetry and sensor data into understandable risk indices through quantitative methods.

---

## [MODEL] 1. Risk Scoring Methodology

The risk score is not a qualitative value but the result of a weighted sum function normalized in the range of $[0, 100]$.

### 1.1. General Formula
$$R = \sum_{i=1}^{n} (w_i \cdot s_i)$$

Where:
- $w_i$: Weight of the $i$-th component, reflecting the importance of that indicator to the overall risk.
- $s_i$: Normalized value of the $i$-th indicator (usually from satellite imagery or radar).

### 1.2. System Weight Allocation
| Indicator ($i$) | Weight ($w_i$) | Analytical Logic |
|---|---|---|
| **Cloud Cover** | 25% | Percentage of surface area covered by thick clouds. |
| **Optical Depth** | 15% | Penetration of the infrared spectrum through the cloud layer. |
| **Rain Rate** | 30% | Integrated data from satellites and the XYZ radar network. |
| **Pressure Delta** | 10% | Deviation from standard pressure ($1013.25\text{ hPa}$). |
| **Growth Gradient** | 20% | Development velocity of cloud masses over the past 60 minutes. |

---

## [DATA] 2. Confidence Metric

To ensure the validity of alerts, each calculation result is accompanied by a confidence value:
$$C = F(t) \cdot P(n)$$

1. **Freshness Function ($F$)**: The older the data, the lower the confidence, decreasing according to the exponential function $e^{-\lambda t}$.
2. **Provenance Consensus ($P$)**: Confidence increases when there is cross-confirmation from multiple sources (e.g., Himawari consensus with terrestrial Radar).

---

## [SEV] 3. Severity Levels & Actions

- **Level 1 (Safe)**: $R < 40$. Stable environmental conditions.
- **Level 2 (Watch)**: $40 \le R < 60$. The system increases scanning frequency and updates status every 5 minutes.
- **Level 3 (High Risk)**: $60 \le R < 80$. Automatically broadcasts WebSockets to affected areas.
- **Level 4 (Critical)**: $R \ge 80$. Activates emergency warning procedures via SMS/Email and overrides system priorities.

![StarWeather Risk Analysis Dashboard](images/intelligence_dashboard.png)
