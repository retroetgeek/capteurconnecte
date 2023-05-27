#include <WiFiUdp.h>
#include <ESP8266WiFi.h>
#include <NTPClient.h>
#include <ESP8266HTTPClient.h>
#include <DHT.h>
#include <WiFiClient.h>
#include <arduino-timer.h>
#include "config.h"

// parametre NTP pour le serveur et parametre de l'heure
const char *ntpServer = "europe.pool.ntp.org";
const long gmtOffset_sec = 3600;
const int daylightOffset_sec = 3600;

#define DHTPIN 4 //pin pour le DHT
DHT dht(DHTPIN, DHT11);

// parametrage NTP date et heure
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, ntpServer, gmtOffset_sec, daylightOffset_sec);

// timer
auto timer = timer_create_default();

// recuperation du temps et heure sur le serveur
bool miseAJourTimeWeb(void *){
    timeClient.update();
    Serial.print("TempsWeb");
    Serial.println(timeClient.getFormattedTime());
    return true; // repeter
}

// Fonction de réinitialisation de la carte ESP8266
bool resetESP8266(void *) {
  ESP.reset();
  delay(5000); // Attendre 5 secondes pour la réinitialisation
  return true; // repeter
}

void setup() {
  Serial.begin(115200);  
  //connexion au wifi
  WiFi.begin(SSID, PASSWORD);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }

  //activation dht
  dht.begin();
  //activation timeClient 
  timeClient.begin();
  // recuperation date et heure
  miseAJourTimeWeb(NULL);

  // creation d'un timer de recuperation date et heure
  timer.every(3600000, miseAJourTimeWeb);
  // creation d'un timer de recuperation capteur
  timer.every(300000, mesuresCapteur);
  // creation d'un timer d'envoie serveur
  timer.every(301000, envoieServeur);

// Création d'un timer pour la réinitialisation tous les 4 jours (4 jours = 345600000 millisecondes)
  timer.every(345600000, resetESP8266);
//timer.every(302000, testServeur); 
}

//fonction de mesure de capteur
bool mesuresCapteur(void *){
  // recuperation valeur dht
   float h = dht.readHumidity();
   float t = dht.readTemperature();

// si valeurs pas ok alors on relance
  while (isnan(h) || isnan(t) || ( h < 0.1 && t < 0.1)) {
    Serial.println("Failed to read valid data from DHT sensor. Retrying...");
    delay(1000); // attendre une seconde avant la prochaine tentative
    h = dht.readHumidity();
    t = dht.readTemperature();
  }
    // envoie des données dans un tableau de stockage (1,h) correspond au capteur 1 de notre serveur
    // (2,t) correspond au capteur 2 de notre serveur
    recordSensorData(1,h);
    recordSensorData(2,t);

  Serial.print((float)t); Serial.print(" *C, "); 
  Serial.print((float)h); Serial.println(" H");
    return true; // repeter
}

// fonction pour envoyer les données sur le serveur
bool sendData(String url) {

// pour debug
//Serial.println(url);
  
  WiFiClient client;
  HTTPClient http; 
  if (!client.connect(HOST, 80)) {
    Serial.println("Wifi absent ou site web ko");
    // fonction pour relancer le wifi quand il est ko
    if (WiFi.status() != WL_CONNECTED) {
      Serial.println("WiFi absent. Reconnexion...");
      WiFi.disconnect();
      WiFi.reconnect();
    }    
    return false;
  }

      String serverPath = url;
      // Your Domain name with URL path or IP address with path
      http.begin(client, serverPath.c_str());
       
      // Send HTTP GET request
      int httpResponseCode = http.GET();
      
      if (httpResponseCode>0) {
        Serial.print("HTTP Response code: ");
        Serial.println(httpResponseCode);
        String payload = http.getString();
        Serial.println(payload);

          if(httpResponseCode != 200){
            http.end();
            return false;
          }
      }
      else {
        Serial.print("Error code: ");
        Serial.println(httpResponseCode);
        http.end();
        return false;
      }
      // Free resources
      http.end();
  
  return true;
}

// structure des données capteur
struct SensorData {
  int capteur_id;
  float data;
  String date;
};

SensorData sensorData[100]; // tableau pour enregistrer les données du capteur
int sensorDataIndex = 0; // index pour enregistrer les données dans le tableau

//fonction pour enregistrer les donnes capteur
void recordSensorData(int capteur_id, float data) {
  if (sensorDataIndex < 100) {
    // enregistrer les données du capteur
    sensorData[sensorDataIndex].capteur_id = capteur_id;
    sensorData[sensorDataIndex].data = data;
    sensorData[sensorDataIndex].date = String(dateNow());
    sensorDataIndex++;
  }
}

// retourne la date formatée
String dateNow(){
    time_t epochTime = timeClient.getEpochTime();
  struct tm *ptm = localtime((const time_t *)&epochTime);
  char buffer[20];
  strftime(buffer, 20, "%Y-%m-%d_%H:%M:%S", ptm);
  return buffer;
}


void loop() {

// pour faire tourner le timer
timer.tick();

}

// fonction d'envoie au serveur
bool envoieServeur(void *){
  for (int i = 0; i < sensorDataIndex; i++) {
    SensorData data = sensorData[i];
    // envoie sur HOST ( a completer dans config ) page reception.php
    String url = String("http://") + String(HOST) + String("/reception.php?capteur_id=") + String(data.capteur_id) + String("&data=") + String(data.data) + String("&date=") + data.date+ String("&key=") + String(KEY);
    
    if (!sendData(url)) {
      // l'envoi a échoué, ne retirez pas l'enregistrement et sortez de la fonction
      return true;
    } else {
      // l'envoi a réussi, retirez l'enregistrement de SensorData
      for (int j = i; j < sensorDataIndex - 1; j++) {
        sensorData[j] = sensorData[j + 1];
      }
      sensorDataIndex--;
      i--;
    }
  }
  return true; // repeter
}



bool testServeur(void *){
  Serial.println("Pile");
  for (int i = 0; i < sensorDataIndex; i++) {
    SensorData data = sensorData[i];
    String url = String("http://") + String(HOST) + String("/reception.php?capteur_id=") + String(data.capteur_id) + String("&data=") + String(data.data) + String("&date=") + data.date;
    
    //Serial.println(url);
  }
  return true; // repeter
}