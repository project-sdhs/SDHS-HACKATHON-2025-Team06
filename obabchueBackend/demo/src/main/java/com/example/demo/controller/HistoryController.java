package com.example.demo.controller;

import com.example.demo.dto.HistoryResponse;
import com.example.demo.entity.History;
import com.example.demo.repositroy.HistoryRepository;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.Parameter;
import io.swagger.v3.oas.annotations.responses.ApiResponse;
import io.swagger.v3.oas.annotations.responses.ApiResponses;
import io.swagger.v3.oas.annotations.media.Content;
import io.swagger.v3.oas.annotations.media.Schema;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

@RestController
public class HistoryController {
    @Autowired
    private HistoryRepository historyRepository;

    @GetMapping("/api/showHistory")
    @Operation(
        summary = "사용자 가계코드 히스토리 조회",
        description = "사용자 ID를 받아 해당 사용자의 가계코드 선택 히스토리를 조회합니다.\n- 최신 선택 순으로 정렬되어 반환됩니다.\n\n요청 예시: /api/showHistory?userId=user123"
    )
    @ApiResponses({
        @ApiResponse(
            responseCode = "200",
            description = "사용자의 가계코드 히스토리 목록 반환",
            content = @Content(schema = @Schema(implementation = HistoryResponse.class))
        ),
        @ApiResponse(
            responseCode = "400",
            description = "잘못된 요청"
        )
    })
    public ResponseEntity<List<HistoryResponse>> showHistory(
        @Parameter(description = "사용자 ID", example = "user123") @RequestParam String userId
    ) {
        if (userId == null || userId.trim().isEmpty()) {
            return ResponseEntity.badRequest().build();
        }

        List<History> histories = historyRepository.findByUserIdOrderBySelectedAtDesc(userId);
        List<HistoryResponse> responses = histories.stream()
            .map(history -> new HistoryResponse(history.getHouseholdCode(), history.getSelectedAt()))
            .collect(Collectors.toList());

        return ResponseEntity.ok(responses);
    }

    @PostMapping("/api/addHistory")
    @Operation(
        summary = "가계코드 히스토리 추가",
        description = "사용자 ID와 가계코드를 받아 히스토리에 새로운 기록을 추가합니다.\n- 선택 시간은 자동으로 현재 시간으로 설정됩니다.\n\n요청 예시: /api/addHistory?userId=user123&householdCode=H001"
    )
    @ApiResponses({
        @ApiResponse(
            responseCode = "200",
            description = "가계코드 히스토리 추가 성공",
            content = @Content(schema = @Schema(example = "{\n  'success': true,\n  'message': '가계코드 히스토리가 추가되었습니다.',\n  'householdCode': 'H001',\n  'selectedAt': '2024-01-15T14:30:00'\n}"))
        ),
        @ApiResponse(
            responseCode = "400",
            description = "잘못된 요청"
        )
    })
    public ResponseEntity<Map<String, Object>> addHistory(
        @Parameter(description = "사용자 ID", example = "user123") @RequestParam String userId,
        @Parameter(description = "가계코드", example = "H001") @RequestParam String householdCode
    ) {
        Map<String, Object> result = new HashMap<>();

        if (userId == null || userId.trim().isEmpty() || householdCode == null || householdCode.trim().isEmpty()) {
            result.put("success", false);
            result.put("message", "사용자 ID와 가계코드를 모두 입력해주세요.");
            return ResponseEntity.badRequest().body(result);
        }

        // 히스토리에 새로운 기록 추가
        History history = new History(userId, householdCode);
        historyRepository.save(history);

        result.put("success", true);
        result.put("message", "가계코드 히스토리가 추가되었습니다.");
        result.put("householdCode", householdCode);
        result.put("selectedAt", history.getSelectedAt());

        return ResponseEntity.ok(result);
    }
} 