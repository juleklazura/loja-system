#!/bin/bash

# Script para executar testes com cobertura e mutação
# Uso: ./run_tests.sh [--coverage] [--mutation] [--all]

set -e

COVERAGE=false
MUTATION=false
ALL=false

# Parse dos argumentos
while [[ $# -gt 0 ]]; do
    case $1 in
        --coverage)
            COVERAGE=true
            shift
            ;;
        --mutation)
            MUTATION=true
            shift
            ;;
        --all)
            ALL=true
            shift
            ;;
        *)
            echo "Uso: $0 [--coverage] [--mutation] [--all]"
            exit 1
            ;;
    esac
done

if [ "$ALL" = true ]; then
    COVERAGE=true
    MUTATION=true
fi

echo "🧪 Executando testes..."

# Criar diretórios necessários
mkdir -p build/coverage
mkdir -p build/infection

echo "📁 Criando diretórios de build..."

# Executar testes unitários
echo "🔧 Executando testes unitários..."
vendor/bin/phpunit --testsuite=Unit --colors=always

if [ "$COVERAGE" = true ]; then
    echo "📊 Executando testes com cobertura..."
    vendor/bin/phpunit --testsuite=Unit --coverage-html=build/coverage/html --coverage-clover=build/coverage/clover.xml --colors=always
    
    echo "📈 Relatório de cobertura gerado em: build/coverage/html/index.html"
fi

# Executar testes de feature se existirem
if [ -d "tests/Feature" ] && [ "$(ls -A tests/Feature)" ]; then
    echo "🌟 Executando testes de feature..."
    vendor/bin/phpunit --testsuite=Feature --colors=always
fi

if [ "$MUTATION" = true ]; then
    echo "🧬 Executando testes de mutação..."
    
    # Verificar se Infection está instalado
    if [ ! -f "vendor/bin/infection" ]; then
        echo "⚠️  Infection não encontrado. Instalando..."
        composer require --dev infection/infection
    fi
    
    # Executar Infection
    vendor/bin/infection --threads=4 --show-mutations --only-covered
    
    echo "🧬 Relatório de mutação gerado em: build/infection-log.html"
fi

echo "✅ Todos os testes concluídos!"

# Exibir resumo
echo ""
echo "📋 RESUMO:"
echo "- Testes unitários: ✅"

if [ "$COVERAGE" = true ]; then
    echo "- Cobertura: ✅ (build/coverage/html/index.html)"
fi

if [ "$MUTATION" = true ]; then
    echo "- Mutação: ✅ (build/infection-log.html)"
fi

echo ""
echo "🎯 Para obter cobertura de 80%+, execute:"
echo "   ./run_tests.sh --coverage"
echo ""
echo "🧬 Para validar qualidade dos testes, execute:"
echo "   ./run_tests.sh --mutation"
echo ""
echo "🚀 Para executar tudo, execute:"
echo "   ./run_tests.sh --all"
