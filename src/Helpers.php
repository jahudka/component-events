<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use InvalidArgumentException;
use Nette\StaticClass;
use ReflectionClass;
use ReflectionMethod;
use ReflectionType;
use ReflectionUnionType;


class Helpers {
    use StaticClass;

    public static function getReturnType(ReflectionMethod $method) : ?string {
        if ($return = (self::getAnnotation($method, 'return') ?? self::getAnnotation($method, 'returns'))) {
            [$return] = preg_split('~\s+~', $return, 2);

            $types = array_filter(
                explode('|', $return),
                fn (string $type) => strpos($type, '[') === false &&
                    !in_array(strtolower($type), ['bool', 'int', 'float', 'string', 'array', 'object', 'callable', 'iterable', 'null', 'false'], true)
            );

            if (count($types) === 1) {
                return self::resolveClassName(reset($types), $method->getFileName());
            }
        } else if ($return = $method->getReturnType()) {
            if (PHP_VERSION_ID >= 80000 && $return instanceof ReflectionUnionType) {
                $types = $return->getTypes();
            } else {
                $types = [$return];
            }

            $types = array_filter($types, fn (ReflectionType $type) => !$type->isBuiltin());

            if (count($types) === 1) {
                return reset($types)->getName();
            }
        }
        return null;
    }

    public static function getAnnotation($reflection, string $name) : ?string {
        if (! ($reflection instanceof ReflectionMethod || $reflection instanceof ReflectionClass)) {
            throw new InvalidArgumentException('Invalid reflection object, expected an instance of ReflectionClass or ReflectionMethod');
        }

        static $cache = [];

        $key = $reflection instanceof ReflectionMethod
            ? sprintf('%s::%s', $reflection->getDeclaringClass()->getName(), $reflection->getName())
            : $reflection->getName();

        if (!isset($cache[$key])) {
            preg_match_all('~@([^\s(]+)(?|\h+(\S.*)|\(([^()]*)\))?~', $reflection->getDocComment() ?: '', $matches, PREG_SET_ORDER);
            $cache[$key] = [];

            foreach ($matches as $match) {
                $cache[$key][] = [$match[1], $match[2] ?? ''];
            }
        }

        foreach ($cache[$key] as $annotation) {
            if ($annotation[0] === $name) {
                return $annotation[1];
            }
        }

        return null;
    }

    public static function resolveClassName(string $class, string $file) : string {
        static $cache = [];

        if ($class[0] === '\\') {
            return substr($class, 1);
        }

        if (!isset($cache[$file])) {
            $src = file_get_contents($file);
            $cache[$file] = [];

            if (preg_match('~\bnamespace\s+([^;]+)~', $src, $m)) {
                $cache[$file]['#'] = trim($m[1]);
            }

            if (preg_match_all('~\buse\s+(.+?(?:\s+as\s+.+?)?(?:\s*,\s*.+?(?:\s+as\s+.+?)?)*);~', $src, $m)) {
                foreach ($m[1] as $imports) {
                    foreach (preg_split('~\s*,\s*~', trim($imports)) as $import) {
                        @list($import, $alias) = preg_split('~\s+as\s+~', $import);
                        $alias = $alias ?? preg_replace('~^.+\\\\~', '', $import);
                        $cache[$file][$alias] = $import;
                    }
                }
            }
        }

        @list($first, $rest) = explode('\\', $class, 2);

        if (isset($cache[$file][$first])) {
            return $cache[$file][$first] . ($rest ? '\\' . $rest : '');
        } else if (isset($cache[$file]['#'])) {
            return $cache[$file]['#'] . '\\' . $class;
        }

        return $class;
    }

}
