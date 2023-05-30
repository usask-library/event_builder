<?php

namespace App\Traits;

trait ExportGraphviz {

    public function toGraphviz() {
        $nodeFormats = [
            'EVENT'  => ',shape=circle,color=forestgreen',
            'OBJECT' => ',shape=circle,color=orange',
            'PERSON' => ',shape=circle,color=deepskyblue',
            'PLACE'  => ',shape=circle,color=mediumpurple',
        ];

        $csvData = $this->toCsvGraph("STRING");

        $graph = [
            'digraph {',
            '  charset="utf-8";'
        ];

        $graph[] = '  // Edges';
        $edges = explode(PHP_EOL, $csvData['edges']);
        $header = array_shift($edges);
        foreach ($edges as $edge) {
            $fields = str_getcsv($edge);
            $graph[] = '  "' . $fields[0] . '" -> "' . $fields[1] . '" [label="' . $fields[2] . '"];';
        }

        $graph[] = '  // Nodes';
        $nodes = explode(PHP_EOL, $csvData['nodes']);
        $header = array_shift($nodes);
        foreach ($nodes as $node) {
            // id, label, type, class
            $fields = str_getcsv($node);
            $graph[] = '  "' . $fields[0] . '" [label="' . addslashes($fields[1]) . '"' . $nodeFormats[$fields[2]] .  '];';
        }

        $graph[] = '}';

        return implode("\n", $graph);
    }

}
