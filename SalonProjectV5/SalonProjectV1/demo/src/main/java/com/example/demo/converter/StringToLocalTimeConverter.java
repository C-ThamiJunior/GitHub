package com.example.demo.converter;

import org.springframework.core.convert.converter.Converter;
import org.springframework.lang.NonNull;
import org.springframework.stereotype.Component;

import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.time.format.DateTimeParseException;

@Component
public class StringToLocalTimeConverter implements Converter<String, LocalTime> {
    @Override
    public LocalTime convert(@NonNull String source) {
        try {
            return LocalTime.parse(source, DateTimeFormatter.ISO_TIME);
        } catch (DateTimeParseException e) {
            throw new IllegalArgumentException("Invalid time format. Please use the format 'HH:mm'.");
        }
    }
}
