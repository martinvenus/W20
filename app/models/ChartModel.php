<?php

/**
 * Data mining system
 * MI-W20 at CZECH TECHNICAL UNIVERSITY IN PRAGUE
 *
 * @copyright  Copyright (c) 2011
 * @package    W20
 * @author     Ján Januška, Jaroslav Líbal, Martin Venuš
 */

/**
 * Class ChartModel
 */
class ChartModel {

    public static function drawChart($nodes, $edges, $times) {

        /*
          Vars:
          max_nodes // max pocet uzlov
          max_edges // max pocet hran

          minuta = 60
          hodina = 3600
          den = 86400

          int mktime ($hour, $minute, $second, $month, $day, $year, $is_dst = -1)
         */

        $maxNodes = max($nodes);
        $maxEdges = max($edges);

        //vynulovanie minut a sekund
        foreach ($times as $index => $unixCas) {
            $times[$index] = self::vynuluj($unixCas, 1, 0, 0);
        }

        //rozsah dni
        $prvyDen = self::vynuluj($times[0], 0, 0, 0);
        $poslednyDen = self::vynuluj($times[sizeof($times) - 1], 0, 0, 0) + 86400; // ziskame zacatek dne + pripocitame jeden den (86400 sekund)
        $pocetHodin = ($poslednyDen - $prvyDen) / 3600; // zjistime pocet hodin od zacatku, do konce.
        $pocetHodinGraf = $pocetHodin - 1; //v grafu chybi posledni hodina vzdy, proto na graf dame o jednu mene.
        $pocetDni = round(($pocetHodin / 24)) + 1; //pocet dni ve vypisu, tj. na konec dame pripiseme jeden den navic. S vela cislami to nejako zblbne a da divne cislo, treba zaokruhlit
        // pole dnu, ktere se zobrazi v grafu
        $dnyArray = array();
        for ($i = 0; $i < $pocetDni; $i++) {
            $dnyArray[] = date("d.n.", $prvyDen + $i * 86400);
        }

        //ulozeni do vypisu
        // vypis dnu
        // priklad: ?chxl=2:|15.3.|16.3.|17.3.|18.3|19.3
        $chxl = "chxl=2:";
        foreach ($dnyArray as $datum) {
            $chxl .= "|" . $datum;
        }

        // delka x vyska grafu
        $vyskaGrafu = 220;
        $rozsahDne = 100;
        $chs = "&chs=";
        $chs .= ( $pocetDni - 1) * $rozsahDne . "x" . $vyskaGrafu;

        // rozmezi hodin mezi dny
        $chxp = "&chxp=2";
        for ($i = 0; $i < $pocetDni; $i++) {
            $chxp .= "," . $i * 24;
        }


        // rozsah jednotlivých os
        $chxr = "&chxr=0,0," . $maxNodes . "|1,0," . $maxEdges . "|2,0," . $pocetHodin;


        // pocet bodov v jednotlivych osach (napriklad na Y-osi je moznost umistit bod na kazdou hodinu)
        $chds = "&chds=0," . $pocetHodinGraf . ",0," . $maxNodes . ",0," . $pocetHodinGraf . ",0," . $maxEdges;

        // body v grafu
        $chd = "&chd=t:";

        $hodinyUdalosti = "";
        $hodnotyNodes = "";
        $hodnotyEdges = "";

        for ($i = 0; $i < sizeof($times); $i++) {
            //hodiny udalosti
            $hodina = ($times[$i] - $prvyDen) / 3600; // rozdil mezi zacatkem a vydeleno na hodiny
            $hodinyUdalosti .= $hodina;
            if ($i != sizeof($times) - 1)
                $hodinyUdalosti .= ",";

            //hodnoty nodes
            $hodnotyNodes .= $nodes[$i];
            if ($i != sizeof($times) - 1)
                $hodnotyNodes .= ",";

            //hodnoty edges
            $hodnotyEdges .= $edges[$i];
            if ($i != sizeof($times) - 1)
                $hodnotyEdges .= ",";
        }
        $chd .= $hodinyUdalosti . "|" . $hodnotyNodes . "|" . $hodinyUdalosti . "|" . $hodnotyEdges;

        // vysrafovane cary (chceme srafovat Y-ovou os, kazdy den jedna cara)
        $chg = "&chg=" . 100 / ($pocetDni - 1) . ",0";

        $url = "http://chart.apis.google.com/chart?"
                . $chxl
                . $chxp
                . $chxr .
                "&chxs=0,3072F3,11.5,0,lt,C2BDDD|1,FF9900,11.5,0,l,676767&chxtc=0,5&chxt=y,r,x&chs=1000x300&cht=lxy&chco=3072F3,FF9900"
                . $chds
                . $chd .
                "&chdl=Nodes|Edges&chdlp=b"
                . $chg .
                "&chls=1|1&chma=5,5,5,25&chm=r,FF0000,0,0,0&chtt=Po%C4%8Det%20u%C5%BEivatel%C5%AF%20a%20vztahy%20mezi%20nimi";

        return $url;
    }

    // funkce zmeni sekundy, minuty, nebo hodiny na 0
    private static function vynuluj($time, $hod = 1, $min = 1, $sec = 1) {
        //$sec, $min, $hod: 1 neznuluje, 0 znuluje
        //$posun: posunie o $posun dni dalej

        $datum = getdate($time);
        if ($hod == 0)
            $datum["hours"] = 0;
        //echo $datum["hours"];
        if ($min == 0)
            $datum["minutes"] = 0;
        if ($sec == 0)
            $datum["seconds"] = 0;
        return mktime($datum["hours"], $datum["minutes"], $datum["seconds"], $datum["mon"], $datum["mday"], $datum["year"]);
    }
}
?>