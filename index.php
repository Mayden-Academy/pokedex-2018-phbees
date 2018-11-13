<?php

/*
 * This is an API call to the pokeAPI and pulls all of the data from 151 pokemon and puts them in an array. thew sleep stops the api call half way through for 60 seconds
 *
 *
 * @result this is an array with a json string in it containing of all of the information of all of the 151 pokemon called
 */
function grabApi () {
    $pokemon = [];
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $i = 0;
    while ($i < 151) {
        if ($i === 75){
            sleep(60);
        }
        $i ++;
        curl_setopt($curl, CURLOPT_URL, 'https://pokeapi.co/api/v2/pokemon/' . $i . '/');
        $resp = curl_exec($curl);
        $pokemon[] = json_decode($resp, TRUE);
    }
    curl_close($curl);
    return $pokemon;
}

$pokemonArray = grabApi();

/** Iterates over the array given to create smaller multidimensional functions of each pokemon
 * @param array $array - This should be the array of pokemon retrieved from the API
 * @return array - an array, each item containing 3 values of name, type 1 & type 2
 */
function getPokeType (array $array) : array {
    $pokemon = [];
    foreach ($array as $eachArray) {
        $name = $eachArray['forms'][0]['name'];
        $type1 = $eachArray['types'][0]['type']['name'];
        $type2 = $eachArray['types'][1]['type']['name'];
        $pokemon[] = [$name, $type1, $type2];
    }
    return $pokemon;
}

$allPokemon = getPokeType($pokemonArray);

$db = new PDO('mysql:host=127.0.0.1;dbname=pokemon', 'root');

/** This function adds pokemon to the database from the API array
 * @param $allPokemon array - This is the array that is broken down in the getPokeType function
 * @param $db - PDO you have
 */
function addPokemon (array $allPokemon, PDO $db) {
    foreach ($allPokemon as $pokemon) {
        $name = $pokemon[0];
        $type1 = $pokemon[1];
        $type2 = $pokemon[2];
        $addPokemon = $db->prepare("INSERT INTO `pokemon` (`pokemon_Name`, `pokemon_type`, `pokemon_type2`) VALUES (:pokemon_Name, :pokemon_type, :pokemon_type2)");
        $addPokemon->bindParam(':pokemon_Name', $name);
        $addPokemon->bindParam(':pokemon_type', $type1);
        $addPokemon->bindParam(':pokemon_type2', $type2);
        $addPokemon->execute();
    }
}

addPokemon($allPokemon, $db);