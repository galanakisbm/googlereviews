# Google Reviews Widget για WordPress

Ένα WordPress plugin που προσθέτει widget για εμφάνιση κριτικών Google μέσω του **Google Places API**.

---

## ✨ Χαρακτηριστικά

- Εμφάνιση κριτικών απευθείας από το Google Places API
- Ρυθμιζόμενος αριθμός κριτικών (1–5)
- Φιλτράρισμα με βάση την ελάχιστη βαθμολογία (αστέρια)
- Cache 12 ωρών με WordPress Transients για γρήγορη φόρτωση
- Responsive σχεδίαση με CSS
- Υποστήριξη ελληνικών και πολλαπλών γλωσσών (i18n ready)

---

## 🚀 Εγκατάσταση

1. Αντιγράψτε τον φάκελο `googlereviews` στον κατάλογο `/wp-content/plugins/` του WordPress σας.
2. Ενεργοποιήστε το plugin από τον πίνακα **Plugins** του WordPress.
3. Μεταβείτε σε **Appearance → Widgets** και σύρετε το widget **Google Reviews** στο sidebar σας.

---

## ⚙️ Ρύθμιση

| Πεδίο | Περιγραφή |
|---|---|
| **Τίτλος** | Τίτλος που εμφανίζεται πάνω από τις κριτικές |
| **Google API Key** | Το κλειδί API από το Google Cloud Console |
| **Google Place ID** | Το μοναδικό ID της επιχείρησής σας στο Google |
| **Μέγιστος αριθμός κριτικών** | Πόσες κριτικές να εμφανίζονται (1–5) |
| **Ελάχιστη βαθμολογία** | Εμφάνιση μόνο κριτικών με τόσα αστέρια ή περισσότερα |

---

## 🔑 Πώς να αποκτήσετε API Key & Place ID

### Google API Key
1. Μεταβείτε στο [Google Cloud Console](https://console.cloud.google.com/)
2. Δημιουργήστε ένα νέο Project (ή επιλέξτε υπάρχον)
3. Ενεργοποιήστε το **Places API**
4. Δημιουργήστε διαπιστευτήρια → **API Key**
5. (Προαιρετικό αλλά συνιστάται) Περιορίστε το API Key σε HTTP referrers του domain σας

### Google Place ID
1. Μεταβείτε στο [Place ID Finder](https://developers.google.com/maps/documentation/places/web-service/place-id)
2. Αναζητήστε την επιχείρησή σας
3. Αντιγράψτε το Place ID (π.χ. `ChIJN1t_tDeuEmsRUsoyG83frY4`)

---

## 📁 Δομή Αρχείων

```
googlereviews/
├── google-reviews-widget.php   # Κύριο αρχείο plugin
├── assets/
│   └── css/
│       └── google-reviews-widget.css   # Στυλ widget
└── README.md
```

---

## 🛡️ Ασφάλεια & Απόδοση

- Όλα τα δεδομένα αποθηκεύονται με `esc_html()` / `esc_attr()` / `esc_url()`
- Τα αποτελέσματα αποθηκεύονται στη cache για **12 ώρες** με WordPress Transients
- Η cache εκκαθαρίζεται αυτόματα κάθε 12 ώρες

---

## 📄 Άδεια Χρήσης

GPL-2.0+