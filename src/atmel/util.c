#include "util.h"
#include <stdlib.h>
#include <util/delay.h>

void delay_us( int delay ) {
    int cnt;

    for ( cnt = 0; cnt < delay; cnt++ )
        _delay_us(1);
}

void delay_ms( int delay ) {
    int cnt;

    for ( cnt = 0; cnt < delay; cnt++ )
        _delay_ms(1);
}


