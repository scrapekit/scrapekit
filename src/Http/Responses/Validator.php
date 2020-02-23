<?php

namespace ScrapeKit\ScrapeKit\Http\Responses;

use ReflectionClass;
use ScrapeKit\ScrapeKit\Http\Requests\Request;
use ScrapeKit\ScrapeKit\Http\Requests\RequestValidation;
use ScrapeKit\ScrapeKit\Http\Responses\Parsers\Concerns\ResponseValidationInterface;

class Validator {

    public static function useParser( $parserClass = null ) {
        if ( $parserClass !== null ) {

            if ( ! ( ( new ReflectionClass( $parserClass ) )->implementsInterface( RequestValidation::class ) ) ) {
                throw new \Exception( $parserClass . ' can not validate requests' );
            }

            return function ( Request $request ) use ( $parserClass ) {
                ( new $parserClass( $request->response() ) )->validate();
            };
        }

        return function ( Request $request ) {
            return $request->response()
                   && $request->response()->parse()
                   && $request->response()->parse()->validate();
        };


    }

    public static function isHtml() {
        return function ( Request $request ) {
            return $request->response()->isHtml();
        };
    }

    public static function isJson() {
        return function ( Request $request ) {
            return $request->response()->isJson();
        };
    }

    public static function isOk() {
        return function ( Request $request ) {
            return $request->response()->isOk();
        };
    }

    public static function status( $input ) {
        return function ( Request $request ) use ( $input ) {
            return $request->response()->status() == $input;
        };
    }

    public static function matchString( $input ) {
        return function ( Request $request ) use ( $input ) {
            return mb_strpos( $request->response()->body(), $input ) !== false;
        };
    }

    public static function matchRegex( $input ) {
        return function ( Request $request ) use ( $input ) {
            return preg_match( $input, $request->response()->body() );
        };
    }

    public static function any( array $input ) {

        return function ( Request $request ) use ( $input ) {

            foreach ( $input as $callback ) {
                if ( ! ! $callback( $request ) ) {
                    return true;
                }
            }

            return false;
        };
    }

    public static function all( array $input ) {

        return function ( Request $request ) use ( $input ) {
            foreach ( $input as $callback ) {
                if ( ! $callback( $request ) ) {
                    return false;
                }
            }

            return true;
        };
    }
}
