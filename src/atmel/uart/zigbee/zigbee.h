#ifndef _ZIGBEE_H_
#define _ZIGBEE_H_

#include "global.h"

#define ZIGBEE_STARTUP_DELAY        5

/* must be power of 2 */
#define ZIGBEE_MAX_PACKET_SIZE		32
#define ZIGBEE_UART_BUFFER_MASK		(ZIGBEE_MAX_PACKET_SIZE - 1)

void zigbeeInit( void );
void zigbeeSendCmd( const BYTE, const BYTE, BYTE * const);
void zigbeeSendFindMisplaced ( const BYTE, BYTE * const, BYTE * const);


#endif /* _ZIGBEE_H_ */

