import java.io.IOException;
import java.io.InputStream;

public class SerialReader implements Runnable 
{
    private InputStream in;
    
    public SerialReader ( InputStream in )
    {
        this.in = in;
    }
    
    public void run ()
    {
        byte[] buffer = new byte[1024];
        byte[] commandBuffer = new byte[32];
    	int cbCount = 0;
        int len = -1;
        try
        {
            while ( ( len = this.in.read(buffer)) > -1 )
            {
            	if( commandBuffer == null ){
            		commandBuffer = new byte[32];
            	}
            	if( len != 0 ){
            		try{ //added error handling
	            		for( int i = 0; i < len; i++){
	            				commandBuffer[cbCount + i] = buffer[i];
	            		}
	            		cbCount += len;
            		}catch( Exception ex ){ commandBuffer = null; cbCount = 0; }
            	}
            	if( cbCount >= 3 && cbCount == commandBuffer[1] ){
            		System.out.print( "RECEIVED: ");
            		for( int i = 0; i < cbCount; i++ ){
            			System.out.print( String.format( "%x ", commandBuffer[i]) );
            		}
            		System.out.println();
            		convert( commandBuffer, cbCount );
            		commandBuffer = null;
            		cbCount = 0;
            	}
            }
        }
        catch ( IOException e )
        {
            e.printStackTrace();
        }            
    }
    
    private void convert( byte[] buffer, int len ){

    	int[] convertedBuffer = new int[len]; //bug fix "len" instead of "32"  This may have caused the incorrect data exception.
        for(int i = 0; i < len; i++){
        	convertedBuffer[i] = buffer[i] & 0xFF;
        }
        SerialCommandHandler parseCommand = new SerialCommandHandler(convertedBuffer, len);
    }
}
