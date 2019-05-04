<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use Nette\StaticClass;


class Helpers {
    use StaticClass;

    public static function getReturnType(\ReflectionMethod $method) : ?string {
        if ($method->hasReturnType() && !$method->getReturnType()->isBuiltin()) {
            return $method->getReturnType()->getName();
        } else if (preg_match('~@returns?\s+(\S+)~', $method->getDocComment() ?: '', $m)) {
            return self::resolveFQCN($m[1], $method->getFileName());
        } else {
            return null;
        }
    }

    private static function resolveFQCN(string $class, string $file) : string {
        static $cache = [];

        if ($class[0] === '\\') {
            return substr($class, 1);
        }

        if (!isset($cache[$file])) {
            $src = file_get_contents($file);
            $cache[$file] = [];

            if (preg_match_all('~\buse\s+(.+?(?:\s+as\s+.+?)?(?:\s*,\s*.+?(?:\s+as\s+.+?)?)*);~', $src, $m)) {
                foreach ($m[1] as $imports) {
                    foreach (preg_split('~\s*,\s*~', trim($imports)) as $import) {
                        @list($import, $alias) = preg_split('~\s+as\s+~', $import);
                        $alias = $alias ?? preg_replace('~^.+\\~', '', $import);
                        $cache[$file][$alias] = $import;
                    }
                }
            }
        }

        @list($first, $rest) = explode($class, '\\', 2);

        if (isset($cache[$file][$first])) {
            return $cache[$file][$first] . ($rest ? '\\' . $rest : '');
        }

        return $class;
    }

}
