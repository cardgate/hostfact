![CardGate](https://cdn.curopayments.net/thumb/200/logos/cardgate.png)

# CardGate module voor WeFact hosting versie **3.3.0+**

[![Total Downloads](https://img.shields.io/packagist/dt/cardgate/wefact.svg)](https://packagist.org/packages/cardgate/wefact)
[![Latest Version](https://img.shields.io/packagist/v/cardgate/wefact.svg)](https://github.com/cardgate/wefact/releases)
[![Build Status](https://travis-ci.org/cardgate/wefact.svg?branch=master)](https://travis-ci.org/cardgate/wefact)

## Support

Deze plug-in is geschikt voor WeFact hosting versie **3.3.0** of hoger

## Voorbereiding

Voor het gebruik van deze module zijn CardGate inloggegevens noodzakelijk.

Ga a.u.b. naar [My Cardgate](https://my.cardgate.com/) en kopieer de  Site ID and Codeersleutel, of vraag deze gegevens aan uw accountmanager.

## Installatie

1. Download en unzip het **cardgate.zip** bestand op je bureaublad.

2. Upload de map **Klanten/betalen** naar de map **/htdocs/Klanten/** van je webshop.

3. Upload de map **klantenbeheer/betalen** naar de map **/htdocs/klantenbeheer/** van je webshop.
  
## Configuratie

1. Login op het **admin** gedeelte van je webshop.

2. Kies **Instellingen, Betaalmogelijkheden**.

3. Klik op de knop **Betaalmethode Toevoegen** en kies dan bij **Soort Betaalmethode: Overige online betaalmethode**. 

4. Kies dan bij **Map Bestanden, cardgate**.

5. Vul de details in bij **Algemeen** en **Instellingen voor de betaalmethode**.

6. Vul de **Site ID** en de **Hash Key (Codeersleutel)** in, deze kun je vinden bij **Sites** op [My Cardgate](https://my.cardgate.com/).

7. De betaalmethoden die voor je **geactiveerd** zijn zullen automatisch in het betaalscherm van WeFact verschijnen.  
   (Indien de instellingen correct zijn ingevuld)

8. Klik op **Betaalmethode toevoegen** om de instellingen op te slaan.

9. Ga naar [My Cardgate](https://my.cardgate.com/), kies **Sites** en selecteer de juiste site.

10. Vul bij **Technische koppeling** de **Callback URL** in, bijvoorbeeld:  
    **http://mijnwebshop.com/klantenbeheer/betalen/cardgate/notify.php**  
    (Vervang **http://mijnwebshop.com** met de URL van je webshop)  

11. Zorg ervoor dat je na het testen de **Mode** omschakelt van **Test Mode** naar **Live mode** en sla het op (**Save**).
 
## Vereisten

Geen verdere vereisten.
