<?php

// Controlador API para buscar instituciones educativas de España
// Combina un listado local extenso con la API del Ministerio de Educación
class ApiInstitucionesController {

    // Buscar instituciones educativas
    public function search() {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=3600');

        // Rate limiting: máximo 30 peticiones por minuto por IP
        if (session_status() === PHP_SESSION_NONE) session_start();
        $rateCheck = checkRateLimit('api_instituciones', 30, 60);
        if ($rateCheck['limited']) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many requests']);
            return;
        }

        $query = trim($_GET['q'] ?? '');
        if (mb_strlen($query) < 2) {
            echo json_encode([]);
            return;
        }

        $results = [];
        $queryLower = mb_strtolower($query);

        // 1. Buscar en el listado local
        $localResults = $this->searchLocal($queryLower);
        $results = array_merge($results, $localResults);

        // 2. Intentar buscar en la API del Ministerio (si hay pocos resultados locales)
        if (count($results) < 5) {
            $apiResults = $this->searchMinisterioAPI($query);
            $results = array_merge($results, $apiResults);
        }

        // Eliminar duplicados y limitar
        $seen = [];
        $unique = [];
        foreach ($results as $r) {
            $key = mb_strtolower($r['nombre']);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $r;
            }
            if (count($unique) >= 15) break;
        }

        echo json_encode($unique, JSON_UNESCAPED_UNICODE);
    }

    // Buscar en el listado local de instituciones
    private function searchLocal(string $queryLower): array {
        $results = [];
        $instituciones = $this->getInstituciones();

        foreach ($instituciones as $inst) {
            if (mb_strpos(mb_strtolower($inst), $queryLower) !== false) {
                $results[] = ['nombre' => $inst];
            }
            if (count($results) >= 15) break;
        }

        return $results;
    }

    // Consultar API del Ministerio de Educacion
    private function searchMinisterioAPI(string $query): array {
        $results = [];

        // Intentar con cURL primero
        if (!function_exists('curl_init')) {
            return $results;
        }

        try {
            $url = 'https://www.educacion.gob.es/centros/buscar.do?' . http_build_query([
                'comboStruct' => 'ccaa',
                'nombreGenerico' => $query,
                'tipoResultado' => 'json',
            ]);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Ride4Study/1.0',
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response && $httpCode === 200) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    // La API puede devolver diferentes estructuras
                    $centros = $data['centros'] ?? $data['results'] ?? $data;
                    if (is_array($centros)) {
                        foreach ($centros as $centro) {
                            if (!is_array($centro)) continue;
                            $nombre = $centro['denominacionEspecifica']
                                ?? $centro['denominacionGenerica']
                                ?? $centro['nombre']
                                ?? null;
                            if ($nombre) {
                                $results[] = ['nombre' => trim($nombre)];
                            }
                            if (count($results) >= 10) break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('API Ministerio error: ' . $e->getMessage());
        }

        return $results;
    }

    // Listado completo de instituciones educativas españolas
    private function getInstituciones(): array {
        return [
            // Universidades públicas
            'Universidad de Sevilla',
            'Universidad de Granada',
            'Universidad de Málaga',
            'Universidad de Huelva',
            'Universidad de Cádiz',
            'Universidad de Córdoba',
            'Universidad de Jaén',
            'Universidad de Almería',
            'Universidad Pablo de Olavide',
            'Universidad Complutense de Madrid',
            'Universidad Autónoma de Madrid',
            'Universidad Carlos III de Madrid',
            'Universidad Politécnica de Madrid',
            'Universidad Rey Juan Carlos',
            'Universidad de Alcalá',
            'Universidad de Barcelona',
            'Universidad Autónoma de Barcelona',
            'Universidad Politécnica de Cataluña',
            'Universidad Pompeu Fabra',
            'Universitat de Girona',
            'Universitat de Lleida',
            'Universitat Rovira i Virgili',
            'Universidad de Valencia',
            'Universidad Politécnica de Valencia',
            'Universidad de Alicante',
            'Universidad Jaume I',
            'Universidad Miguel Hernández',
            'Universidad de Zaragoza',
            'Universidad de Salamanca',
            'Universidad de Valladolid',
            'Universidad de Santiago de Compostela',
            'Universidad de A Coruña',
            'Universidad de Vigo',
            'Universidad del País Vasco',
            'Universidad de Navarra',
            'Universidad Pública de Navarra',
            'Universidad de Murcia',
            'Universidad Politécnica de Cartagena',
            'Universidad de Castilla-La Mancha',
            'Universidad de Extremadura',
            'Universidad de Cantabria',
            'Universidad de Oviedo',
            'Universidad de La Laguna',
            'Universidad de Las Palmas de Gran Canaria',
            'Universidad de las Islas Baleares',
            'Universidad de La Rioja',
            'Universidad de León',
            'Universidad de Burgos',
            'UNED',
            'Universidad Internacional de Andalucía',
            'Universidad Internacional Menéndez Pelayo',

            // Universidades privadass
            'Universidad Loyola Andalucía',
            'Universidad Europea de Madrid',
            'Universidad Alfonso X el Sabio',
            'Universidad San Pablo CEU',
            'Universidad Pontificia Comillas',
            'Universidad de Nebrija',
            'Universidad Camilo José Cela',
            'IE University',
            'Universidad Ramon Llull',
            'Universitat Oberta de Catalunya',
            'Universidad de Mondragón',
            'Universidad Pontificia de Salamanca',
            'Universidad Católica de Valencia',
            'Universidad Cardenal Herrera CEU',
            'Universidad de Deusto',
            'Universidad Francisco de Vitoria',
            'Universidad Europea de Valencia',
            'Universidad Católica de Ávila',
            'Universidad Católica San Antonio de Murcia',
            'Universidad San Jorge',
            'Universidad Abat Oliba CEU',
            'Universidad Internacional de La Rioja',
            'Universidad a Distancia de Madrid',
            'Universidad Villanueva',

            // Institutos de Educación Secundaria (IES) y Centros de Formación Profesional (FP) de Andalucía
            'IES La Arboleda',
            'IES La Rábida',
            'IES Diego de Guzmán y Quesada',
            'IES San Sebastián',
            'IES Pablo Neruda',
            'IES Fuentepiña',
            'IES Alto Conquero',
            'IES Estuaria',
            'IES La Marisma',
            'IES Rafael Reyes',
            'IES Saltés',
            'IES Don Bosco',
            'IES Padre José Miravent',
            'IES El Sur',
            'IES Doñana',
            'IES Juan Ramón Jiménez',
            'IES San Juan del Puerto',
            'IES Guadiana',
            'IES Sierra de Aracena',
            'IES Cuenca Minera',
            'IES Odiel',
            'IES Delgado Hernández',
            'IES Sebastián Fernández',
            'IES Alborán',
            'IES Al-Ándalus',
            'IES Averroes',
            'IES Blas Infante',
            'IES Fernando III El Santo',
            'IES Galileo Galilei',
            'IES Gran Capitán',
            'IES Luis de Góngora',
            'IES Maimónides',
            'IES Medina Azahara',
            'IES Séneca',
            'IES Alhaken II',
            'IES López Neyra',
            'IES Zaidín-Vergeles',
            'IES Padre Suárez',
            'IES Cartuja',
            'IES Generalife',
            'IES Politécnico Hermenegildo Lanz',
            'IES La Madraza',
            'IES Ángel Ganivet',
            'IES Celia Viñas',
            'IES Maestro Padilla',
            'IES El Argar',
            'IES Los Ángeles',
            'IES Auringis',
            'IES Virgen del Carmen',
            'IES Santa Catalina de Alejandría',
            'IES El Valle',
            'IES Cástulo',
            'IES Los Cerros',
            'IES Portada Alta',
            'IES Sierra Bermeja',
            'IES Huelin',
            'IES Universidad Laboral',
            'IES Gerald Brenan',
            'IES Vicente Espinel',
            'IES Campanillas',
            'IES El Palo',
            'IES Politécnico Jesús Marín',
            'IES Santa Bárbara',
            'IES Federico Mayor Zaragoza',
            'IES Nervión',
            'IES San Pablo',
            'IES Albert Einstein',
            'IES Heliópolis',
            'IES Joaquín Turina',
            'IES Murillo',
            'IES Polígono Sur',
            'IES Ramón del Valle Inclán',
            'IES Torreblanca',
            'IES Triana',
            'IES V Centenario',
            'IES Velázquez',
            'IES Martínez Montañés',
            'IES Gustavo Adolfo Bécquer',
            'IES Luca de Tena',
            'IES Punta del Verde',
            'IES San Jerónimo',
            'IES Ítaca',
            'IES Fernando de Herrera',
            'IES Antonio Domínguez Ortiz',
            'IES La Algaba',

            // Institutos de Educación Secundaria (IES) de Madrid
            'IES San Mateo',
            'IES Lope de Vega',
            'IES Cervantes',
            'IES Isabel la Católica',
            'IES Ramiro de Maeztu',
            'IES Beatriz Galindo',
            'IES San Isidro',
            'IES Conde de Orgaz',
            'IES Barrio de Bilbao',
            'IES Francisco de Quevedo',
            'IES Gregorio Marañón',
            'IES Príncipe Felipe',
            'IES Vallecas-Magerit',
            'IES Madrid Sur',
            'IES Gran Capitán',
            'IES Virgen de la Paloma',
            'IES Renacimiento',
            'IES Pradolongo',
            'IES Numancia',

            // Institutos de Educación Secundaria (IES) de Barcelona
            'IES Jaume Balmes',
            'IES Joan Brossa',
            'IES Montjuïc',
            'IES Poeta Maragall',
            'IES XXV Olimpíada',
            'IES Narcís Monturiol',
            'IES Flos i Calcat',
            'IES Joan Oró',
            'IES Manuel de Pedrolo',

            // Institutos de Educación Secundaria (IES) de Valencia
            'IES Luis Vives',
            'IES San Vicente Ferrer',
            'IES Benlliure',
            'IES Sorolla',
            'IES El Cabanyal',
            'IES Lluís Vives',
            'IES Abastos',
            'IES Jordi de Sant Jordi',
            'IES Figueras Pacheco',
            'IES Jorge Juan',

            // Institutos de Educación Secundaria (IES) de Galicia
            'IES Rosalía de Castro',
            'IES Arcebispo Xelmírez I',
            'IES Eduardo Pondal',
            'IES Fernando Esquío',
            'IES Universidade Laboral',

            // Institutos de Educación Secundaria (IES) del País Vasco
            'IES Miguel de Unamuno',
            'IES Emilio Campuzano',
            'IES Eskurtze',

            // Centros de Formación Profesional (FP)
            'CIFP Hespérides',
            'CIFP Carlos III',
            'CIFP José Luis Garci',
            'CIFP Virgen de Gracia',
            'CIFP Anxel Casal',
            'CIFP A Xunqueira',
            'CIFP César Manrique',
            'CIFP Profesor Raúl Vázquez',
            'CIFP Majada Marcial',
            'CIFP La Laboral',
            'Centro de FP San Viator',
            'Centro de FP Cesur',
            'Centro de FP Ilerna',
            'Centro de FP Medac',
            'Centro de FP Linkia FP',
            'Centro de FP iFP',
        ];
    }
}
