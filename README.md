![CardGate](https://cdn.curopayments.net/thumb/200/logos/cardgate.png)

# CardGate module voor HostFact hosting versie **5.0.0+**

[![Build Status](https://travis-ci.org/cardgate/hostfact.svg?branch=master)](https://travis-ci.org/cardgate/hostfact)

## Support

Deze plug-in is geschikt voor HostFact versie **5.0.0** of hoger

## Voorbereiding

Voor het gebruik van deze module zijn CardGate inloggegevens noodzakelijk.

Bezoek hiervoor [Mijn CardGate](https://my.cardgate.com/) en haal daar je gegevens op,  
of neem contact op met je accountmanager.

## Installatie

1. Download en unzip de meest recente [source code](https://github.com/cardgate/hostfact/releases/) op je bureaublad.

3. Upload de map **klantenbeheer/betalen** naar de map **/htdocs/klantenbeheer/** van je webshop.
  
## Configuratie

1. Login op het **admin** gedeelte van je webshop.

2. Kies **Instellingen, Betaalmogelijkheden**.

3. Klik op de knop **Betaalmethode Toevoegen** en kies dan bij **Soort Betaalmethode: Overige online betaalmethode**. 

4. Kies dan bij **Map Bestanden, cardgate**.

5. Vul de details in bij **Algemeen** en **Instellingen voor de betaalmethode**.

6. Vul de **site ID** en de **hash key** in, deze kun je vinden bij **Sites** op [Mijn CardGate](https://my.cardgate.com/).

7. De betaalmethoden die voor je **geactiveerd** zijn zullen automatisch in het betaalscherm van WeFact verschijnen.  
   (Indien de instellingen correct zijn ingevuld.)

8. Klik op **Betaalmethode toevoegen** om de instellingen op te slaan.

9. Ga naar [Mijn CardGate](https://my.cardgate.com/), kies **Sites** en selecteer de juiste site.

10. Vul bij **Technische koppeling** de **Callback URL** in, bijvoorbeeld:  
    **http://mijnwebshop.com/klantenpaneel/betalen/cardgate/notify.php**  
    (Vervang **http://mijnwebshop.com** met de URL van je webshop)  

11. Zorg ervoor dat je na het testen de **Mode** omschakelt van **Test Mode** naar **Live mode** en sla het op (**Save**).
 
## Vereisten

Geen verdere vereisten.
